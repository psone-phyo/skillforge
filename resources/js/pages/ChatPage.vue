<script setup lang="ts">
import '../../css/frontend/style.css';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import customParseFormat from 'dayjs/plugin/customParseFormat';
import { ref, reactive, computed, onMounted, nextTick } from 'vue';
import axios from 'axios';
import Pusher from 'pusher-js';

dayjs.extend(relativeTime);
dayjs.extend(customParseFormat);

// Breadcrumbs
const breadcrumbs = [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Messages' },
];

// Types
type User = { id: number | string; name: string; avatar?: string };
type Conversation = {
  id: number | string;
  title?: string;
  participants: User[];
  last_message?: { body: string; created_at: string };
  unread_count?: number;
  updated_at?: string;
  is_instructor?: boolean;
};
type Message = {
  id: number | string;
  conversation_id: number | string;
  sender: User;
  body: string;
  created_at: string;
  attachments?: { id?: string|number; name: string; url?: string; size?: number }[];
  read_at?: string | null;
};
const props = defineProps<{
    me: any
}>();
// Current user (replace with your auth prop if available)
const me = ref<User | null>(props.me);

// Conversations list
const conversations = ref<Conversation[]>([]);
const conversationsLoading = ref(false);
const conversationsSearch = ref('');
const selectedConvId = ref<number | string | null>(null);

// Messages
const messages = ref<Message[]>([]);
const messagesLoading = ref(false);
const messagesEnd = ref(false);
const messagesPage = ref(1);
const pageSize = 30;

// Composer
const composing = reactive({
  text: '',
  files: [] as File[],
  sending: false,
});


// Partner typing indicator (only show when the OTHER user is typing)
const partnerTyping = ref(false);
let partnerTypingTimer: any = null;

// Refs
const listRef = ref<HTMLDivElement | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

// Pusher client and current channel
const pusherRef = ref<Pusher | null>(null);
let currentChannel: Pusher.Channel | null = null;

// Derived
const activeConversation = computed(() =>
  conversations.value.find(c => c.id === selectedConvId.value) || null
);

const filteredConversations = computed(() => {
  const q = conversationsSearch.value.trim().toLowerCase();
  if (!q) return conversations.value;
  return conversations.value.filter(c => {
    const title = c.title || otherPartyName(c) || '';
    return title.toLowerCase().includes(q);
  });
});

function otherParty(conversation: Conversation): User | null {
  if (!me.value) return conversation.participants?.[0] || null;
  return (conversation.participants || []).find(u => String(u.id) !== String(me.value!.id)) || null;
}
function otherPartyName(conversation: Conversation): string {
  const u = otherParty(conversation);
  return u?.name || 'Conversation';
}
function otherPartyAvatar(conversation: Conversation): string | undefined {
  return otherParty(conversation)?.avatar;
}

// Fetch current user if needed
async function fetchCurrentUser() {
  // Wire to your auth source if needed
}

// Backend: conversations
async function fetchConversations() {
  conversationsLoading.value = true;
  try {
    const { data } = await axios.get('/conversations', {
      params: { q: conversationsSearch.value || undefined },
    });
    conversations.value = data || [];
    if (!selectedConvId.value && conversations.value.length) {
      selectConversation(conversations.value[0].id);
    }
  } finally {
    conversationsLoading.value = false;
  }
}

// Backend: messages (paged)
async function fetchMessages(conversationId: number | string, page = 1) {
  if (messagesLoading.value || messagesEnd.value) return;
  messagesLoading.value = true;
  try {
    const { data } = await axios.get(`/conversations/${conversationId}/messages`, {
      params: { page, per_page: pageSize },
    });

    const items: Message[] = Array.isArray(data) ? data : (data?.data || []);
    if (page === 1) {
      messages.value = items.reverse(); // newest last
      await nextTick();
      scrollToBottom();
    } else {
      const prevHeight = listRef.value?.scrollHeight || 0;
      messages.value = [...items.reverse(), ...messages.value];
      await nextTick();
      const newHeight = listRef.value?.scrollHeight || 0;
      const el = listRef.value;
      if (el) el.scrollTop = newHeight - prevHeight;
    }

    if (!Array.isArray(data) && (!data?.meta?.next_page || items.length < pageSize)) {
      messagesEnd.value = true;
    }
    messagesPage.value = page;

    // Mark as read
    await axios.post(`/conversations/${conversationId}/read`);
  } finally {
    messagesLoading.value = false;
  }
}

function selectConversation(id: number | string) {
  if (selectedConvId.value === id) return;
  selectedConvId.value = id;
  messages.value = [];
  messagesEnd.value = false;
  messagesPage.value = 1;
  partnerTyping.value = false;
  clearTimeout(partnerTypingTimer);
  void fetchMessages(id, 1);
  subscribeToChannel(id);
}

// Pusher subscribe/unsubscribe
function subscribeToChannel(conversationId: number | string) {
  if (!pusherRef.value) return;

  // Unsubscribe previous
  if (currentChannel) {
    try { pusherRef.value.unsubscribe(currentChannel.name); } catch {}
    currentChannel = null;
  }

  const channelName = `chat.${conversationId}`;
  const ch = pusherRef.value.subscribe(channelName);
  currentChannel = ch;

  // If server event uses broadcastAs('message.sent')
  ch.bind('message.sent', (data: any) => {
    if (String(selectedConvId.value) !== String(conversationId)) return;
    messages.value = [...messages.value, data];
    nextTick().then(scrollToBottom);
  });

  // Optional: partner typing (requires server to broadcast 'typing' from partner)
  ch.bind('typing', (payload: any) => {
    // Expect payload like { user_id: number, conversation_id: number }
    if (!payload) return;
    const isPartner = String(payload.user_id) !== String(me.value?.id);
    const sameConv = String(payload.conversation_id) === String(selectedConvId.value);
    if (isPartner && sameConv) {
      partnerTyping.value = true;
      clearTimeout(partnerTypingTimer);
      partnerTypingTimer = setTimeout(() => (partnerTyping.value = false), 1500);
    }
  });
}

async function loadOlder() {
  if (!selectedConvId.value || messagesLoading.value || messagesEnd.value) return;
  await fetchMessages(selectedConvId.value, messagesPage.value + 1);
}

function onScrollMessages(e: Event) {
  const el = e.target as HTMLElement;
  if (el.scrollTop < 80 && !messagesEnd.value && !messagesLoading.value) {
    loadOlder();
  }
}

function scrollToBottom() {
  const el = listRef.value;
  if (!el) return;
  el.scrollTop = el.scrollHeight;
}

// Composer
function onPickFiles() {
  fileInputRef.value?.click();
}
function onFilesSelected(e: Event) {
  const files = (e.target as HTMLInputElement).files;
  composing.files = files ? Array.from(files).slice(0, 5) : [];
}

let typingDebounce: any = null;
function onTyping() {
  // Emit typing to server (optional; implement a route to broadcast typing event)
  // Debounce to avoid flooding
  if (!pusherRef.value) return;
  clearTimeout(typingDebounce);
  typingDebounce = setTimeout(() => {
    if (!selectedConvId.value) return;
    const socketId = pusherRef.value!.connection.socket_id; // 👈 get the socket ID

    // Uncomment when backend exists:
    axios.post(`/conversations/${selectedConvId.value}/typing`, {
      conversation_id: selectedConvId.value,
      user_id: me.value?.id,
    },
      {
        headers: {
          'X-Socket-ID': socketId, // 👈 send it to Laravel
        },
      });
  }, 250);
}

async function sendMessage() {
  if (!selectedConvId.value || composing.sending) return;
  const text = composing.text.trim();
  const hasFiles = composing.files.length > 0;
  if (!text && !hasFiles) return;

  const tempId = `temp-${Date.now()}`;
  const tempMessage = {
    id: tempId,
    conversation_id: selectedConvId.value,
    sender: me.value,
    body: text,
    created_at: new Date().toISOString(),
    status: 'sending', // 👈 UX flag
  };

  // Show immediately
  messages.value = [...messages.value, tempMessage];
  await nextTick();
  scrollToBottom();

  try {
    composing.sending = true;
    const socketId = pusherRef.value?.connection.socket_id;

    const { data } = await axios.post(
      '/messages',
      {
        conversation_id: selectedConvId.value,
        body: text,
      },
      {
        headers: {
          'X-Socket-ID': socketId,
        },
      }
    );

    // Replace the temporary message with the real one
    messages.value = messages.value.map((m) =>
      m.id === tempId ? data : m
    );
  } catch (err) {
    // Mark as failed
    messages.value = messages.value.map((m) =>
      m.id === tempId ? { ...m, status: 'failed' } : m
    );
  } finally {
    composing.sending = false;
    composing.text = '';
    composing.files = [];
  }
}


function fmtTime(ts: string) {
  return dayjs(ts).format('MMM D, HH:mm');
}
function isMine(m: Message) {
  return me.value && String(m.sender.id) === String(me.value.id);
}

onMounted(async () => {
  await fetchCurrentUser();
  await fetchConversations();

  // Initialize Pusher client (use your real key/cluster)
  pusherRef.value = new Pusher('9d48a6e508f9c81b7bdd', {
    cluster: 'ap1',
    forceTLS: true,
  });

  // Subscribe initial conversation if exists
  if (selectedConvId.value) {
    subscribeToChannel(selectedConvId.value!);
  }
});
</script>

<template>
  <Head title="Messages" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <main class="py-6">
      <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-[320px_1fr] gap-4">
          <!-- Sidebar -->
          <aside class="rounded-2xl border border-white/10 bg-[linear-gradient(180deg,#1b2240,#141b33)] shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)] overflow-hidden">
            <div class="p-3 border-b border-white/10">
              <div class="text-white font-extrabold">Messages</div>
              <div class="mt-3 flex items-center gap-2 bg-[#0f152c] border border-white/10 rounded-xl p-2">
                <svg width="18" height="18" fill="#A6B3E8" viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27a6.471 6.471 0 0 0 1.57-4.23 6.5 6.5 0 1 0-6.5 6.5 6.471 6.471 0 0 0 4.23-1.57l.27.28v.79l5 5L20.49 19l-5-5Zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14Z"/></svg>
                <input
                  v-model="conversationsSearch"
                  @input="fetchConversations"
                  placeholder="Search"
                  class="flex-1 bg-transparent outline-none text-white placeholder:text-[#AAB3D0] text-[15px]"
                />
              </div>
            </div>

            <div class="max-h-[calc(100vh-260px)] overflow-y-auto">
              <div v-if="conversationsLoading" class="p-4 text-[#9AA6D7]">Loading…</div>
              <div v-else>
                <button
                  v-for="c in filteredConversations"
                  :key="c.id"
                  @click="selectConversation(c.id)"
                  :class="[
                    'w-full flex items-center gap-3 px-3 py-3 text-left border-b border-white/5 hover:bg-[#121936]',
                    selectedConvId === c.id ? 'bg-[#101733]' : ''
                  ]"
                >
                  <img
                    :src="otherPartyAvatar(c) || 'https://placehold.co/80x80/1b2240/FFFFFF?text=SF'"
                    alt=""
                    class="w-12 h-12 rounded-xl border border-white/10 object-cover"
                  />
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                      <div class="text-white font-semibold truncate">
                        {{ c.title || otherPartyName(c) }}
                      </div>
                      <div class="text-xs text-[#9AA6D7] whitespace-nowrap">
                        {{ c.updated_at ? dayjs(c.updated_at).fromNow() : '' }}
                      </div>
                    </div>
                    <div class="text-xs text-[#9AA6D7] truncate">
                      {{ c.last_message?.body || 'No messages yet' }}
                    </div>
                  </div>
                  <span
                    v-if="c.unread_count"
                    class="text-xs px-2 py-0.5 rounded-full border border-emerald-300/40 text-emerald-200 bg-emerald-300/10"
                  >{{ c.unread_count }}</span>
                </button>

                <div v-if="!filteredConversations.length" class="p-4 text-[#9AA6D7]">
                  No conversations
                </div>
              </div>
            </div>
          </aside>

          <!-- Chat -->
          <section class="rounded-2xl border border-white/10 bg-[linear-gradient(180deg,#1b2240,#141b33)] shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)] flex flex-col h-[80vh]">
            <!-- Header -->
            <div class="px-4 py-3 border-b border-white/10 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <img
                  v-if="activeConversation"
                  :src="otherPartyAvatar(activeConversation) || 'https://placehold.co/80x80/1b2240/FFFFFF?text=SF'"
                  class="w-10 h-10 rounded-xl border border-white/10 object-cover"
                />
                <div>
                  <div class="text-white font-semibold">
                    {{ activeConversation ? (activeConversation.title || otherPartyName(activeConversation)) : 'Select a conversation' }}
                  </div>
                  <div class="text-xs text-[#9AA6D7]">
                    {{ activeConversation ? 'Chat with ' + (otherPartyName(activeConversation) || 'instructor') : '' }}
                  </div>
                </div>
              </div>
              <div class="text-xs text-[#9AA6D7]">
                {{ activeConversation?.updated_at ? 'Updated ' + dayjs(activeConversation.updated_at).fromNow() : '' }}
              </div>
            </div>

            <!-- Messages (with animation) -->
            <div
              ref="listRef"
              class="flex-1 overflow-y-auto px-3 py-4"
              @scroll.passive="onScrollMessages"
            >
              <transition-group name="msg" tag="div" class="space-y-2">
                <div
                  v-for="m in messages"
                  :key="m.id"
                  class="flex items-end gap-2"
                  :class="isMine(m) ? 'justify-end pl-12' : 'justify-start pr-12'"
                >
                  <img
                    v-if="!isMine(m)"
                    :src="m.sender.avatar || 'https://placehold.co/80x80/1b2240/FFFFFF?text=SF'"
                    class="w-8 h-8 rounded-lg border border-white/10 object-cover"
                  />
                  <div
                    :class="[
                      'max-w-[78%] rounded-2xl px-3 py-2 border text-sm leading-relaxed relative',
                      isMine(m)
                        ? 'bg-[#5B8CFF] text-white border-white/10 rounded-br-md'
                        : 'bg-[#101733] text-white border-white/10 rounded-bl-md'
                    ]"
                  >
                    <!-- bubble tail -->
                    <span
                      :class="[
                        'absolute bottom-0 w-3 h-3',
                        isMine(m) ? 'right-[-6px] bg-[#5B8CFF] rounded-br-sm' : 'left-[-6px] bg-[#101733] rounded-bl-sm'
                      ]"
                      style="clip-path: polygon(0 100%, 100% 100%, 100% 0); border: 1px solid rgba(255,255,255,0.1)"
                    ></span>

                    <div v-if="m.attachments?.length" class="mb-1 space-y-1">
                      <a
                        v-for="att in m.attachments"
                        :key="att.id || att.name"
                        :href="att.url"
                        target="_blank"
                        class="block text-xs underline text-[#D9E2FF]"
                      >
                        {{ att.name }}
                      </a>
                    </div>
                    <div v-if="m.body">{{ m.body }}</div>
                    <div class="text-[11px] opacity-70 mt-1 text-right">
                      {{ fmtTime(m.created_at) }}
                    </div>
                  </div>
                </div>
              </transition-group>

              <!-- Partner typing (left side only) -->
              <div v-if="partnerTyping && activeConversation" class="flex items-center gap-2 text-[#9AA6D7] text-xs px-2 pt-1 justify-start">
                <span class="inline-flex w-2 h-2 rounded-full bg-[#9AA6D7] animate-pulse"></span>
                {{ otherPartyName(activeConversation) }} is typing…
              </div>
            </div>

            <!-- Composer -->
            <div class="border-t border-white/10 p-3">
              <div class="flex items-end gap-2">
                <button
                  class="px-3 py-2 rounded-lg border border-white/10 text-white bg-[#1b2240] hover:bg-[#141b33]"
                  title="Attach files"
                  @click="onPickFiles"
                >
                  📎
                </button>
                <input ref="fileInputRef" type="file" class="hidden" multiple @change="onFilesSelected" />
                <div class="flex-1">
                  <textarea
                    rows="1"
                    class="w-full bg-[#0b1024] border border-white/10 rounded-xl px-3 py-2 text-white placeholder:text-[#9AA6D7] resize-none"
                    placeholder="Write a message…"
                    v-model="composing.text"
                    @input="onTyping"
                    @keydown.enter.exact.prevent="sendMessage"
                  />
                  <div v-if="composing.files.length" class="flex flex-wrap gap-2 mt-2">
                    <span
                      v-for="f in composing.files"
                      :key="f.name + f.size"
                      class="text-xs px-2 py-1 rounded-md border border-white/10 text-[#C3CCF3] bg-[#1C2340]"
                    >
                      {{ f.name }}
                    </span>
                  </div>
                </div>
                <button
                  class="px-4 py-2 rounded-lg font-semibold text-white border border-white/20 disabled:opacity-60"
                  style="background:linear-gradient(180deg,#5B8CFF,#7B61FF);"
                  :disabled="composing.sending || (!composing.text.trim() && !composing.files.length) || !activeConversation"
                  @click="sendMessage"
                >
                  {{ composing.sending ? 'Sending…' : 'Send' }}
                </button>
              </div>
            </div>
          </section>
        </div>
      </div>
    </main>
  </AppLayout>
</template>

<style scoped>
/* Message appear animation */
.msg-enter-active,
.msg-leave-active {
  transition: all 220ms ease;
}
.msg-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.msg-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>

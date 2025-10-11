<script setup lang="ts">
import '../../css/frontend/style.css';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import customParseFormat from 'dayjs/plugin/customParseFormat';
import { ref, reactive, computed, onMounted, nextTick } from 'vue';
// import axios from 'axios'; // Demo mode: keep commented so you can re-enable later

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

// Demo user (pretend current user)
const me = ref<User | null>({
  id: 1,
  name: 'You',
  avatar: 'https://images.unsplash.com/photo-1607746882042-944635dfe10e?q=80&w=200&auto=format&fit=crop'
});

// Conversations list (demo)
const conversations = ref<Conversation[]>([]);
const conversationsLoading = ref(false);
const conversationsSearch = ref('');
const selectedConvId = ref<number | string | null>(null);

// Messages (demo)
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
  typing: false,
});

const typingIndicator = ref(false);
let typingTimer: any = null;

// Refs
const listRef = ref<HTMLDivElement | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

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
  return (conversation.participants || []).find(u => String(u.id) !== String(me.value!.id)) || conversation.participants?.[0] || null;
}
function otherPartyName(conversation: Conversation): string {
  const u = otherParty(conversation);
  return u?.name || 'Conversation';
}
function otherPartyAvatar(conversation: Conversation): string | undefined {
  return otherParty(conversation)?.avatar;
}

// DEMO HELPERS
function demoConversations(): Conversation[] {
  const instructorA: User = { id: 2, name: 'Alex Rivera (Instructor)', avatar: 'https://images.unsplash.com/photo-1547425260-76bcadfb4f2c?q=80&w=200&auto=format&fit=crop' };
  const instructorB: User = { id: 3, name: 'Sofia Lee (Instructor)', avatar: 'https://images.unsplash.com/photo-1502685104226-ee32379fefbe?q=80&w=200&auto=format&fit=crop' };

  return [
    {
      id: 101,
      title: 'React Patterns — Q&A',
      participants: [me.value!, instructorA],
      last_message: { body: 'See you in the next session!', created_at: dayjs().subtract(10, 'minute').toISOString() },
      unread_count: 0,
      updated_at: dayjs().subtract(10, 'minute').toISOString(),
      is_instructor: true
    },
    {
      id: 102,
      title: 'Data Science Mentoring',
      participants: [me.value!, instructorB],
      last_message: { body: 'Upload your notebook when ready.', created_at: dayjs().subtract(2, 'hour').toISOString() },
      unread_count: 2,
      updated_at: dayjs().subtract(2, 'hour').toISOString(),
      is_instructor: true
    }
  ];
}

function demoMessages(conversationId: number | string, page: number): Message[] {
  // page 1 returns latest; page 2 returns older; stop after page 2
  const alex: User = { id: 2, name: 'Alex Rivera (Instructor)', avatar: 'https://images.unsplash.com/photo-1547425260-76bcadfb4f2c?q=80&w=200&auto=format&fit=crop' };
  const sofia: User = { id: 3, name: 'Sofia Lee (Instructor)', avatar: 'https://images.unsplash.com/photo-1502685104226-ee32379fefbe?q=80&w=200&auto=format&fit=crop' };

  if (conversationId === 101) {
    if (page === 1) {
      return [
        { id: 'm-101-6', conversation_id: 101, sender: alex, body: 'See you in the next session!', created_at: dayjs().subtract(10, 'minute').toISOString() },
        { id: 'm-101-5', conversation_id: 101, sender: me.value!, body: 'Great, thanks a lot!', created_at: dayjs().subtract(12, 'minute').toISOString() },
        { id: 'm-101-4', conversation_id: 101, sender: alex, body: 'Yes, check the repo. I pushed an example of Suspense + SWR.', created_at: dayjs().subtract(15, 'minute').toISOString() },
        { id: 'm-101-3', conversation_id: 101, sender: me.value!, body: 'Do we have a code sample for SWR integration?', created_at: dayjs().subtract(18, 'minute').toISOString() },
        { id: 'm-101-2', conversation_id: 101, sender: alex, body: 'Welcome to React Patterns Q&A!', created_at: dayjs().subtract(25, 'minute').toISOString() },
      ];
    }
    if (page === 2) {
      return [
        { id: 'm-101-1', conversation_id: 101, sender: me.value!, body: 'Hi Alex!', created_at: dayjs().subtract(1, 'day').toISOString() },
      ];
    }
  }

  if (conversationId === 102) {
    if (page === 1) {
      return [
        { id: 'm-102-5', conversation_id: 102, sender: sofia, body: 'Upload your notebook when ready.', created_at: dayjs().subtract(2, 'hour').toISOString() },
        { id: 'm-102-4', conversation_id: 102, sender: me.value!, body: 'Will do, thanks!', created_at: dayjs().subtract(2, 'hour').toISOString() },
        { id: 'm-102-3', conversation_id: 102, sender: sofia, body: 'Try stratified split for better validation.', created_at: dayjs().subtract(3, 'hour').toISOString() },
        { id: 'm-102-2', conversation_id: 102, sender: me.value!, body: 'I’m getting overfitting on the validation set.', created_at: dayjs().subtract(4, 'hour').toISOString() },
        { id: 'm-102-1', conversation_id: 102, sender: sofia, body: 'Hey! How is the model training going?', created_at: dayjs().subtract(5, 'hour').toISOString() },
      ];
    }
    if (page === 2) {
      return [];
    }
  }

  return [];
}

// API placeholders switched to DEMO data
async function fetchCurrentUser() {
  // DEMO: already set in `me`
  // Real API:
  // const { data } = await axios.get('/api/me');
  // me.value = data;
}

async function fetchConversations() {
  conversationsLoading.value = true;
  try {
    // DEMO DATA
    const data = demoConversations();
    // Real API (commented):
    // const { data } = await axios.get('/api/conversations', {
    //   params: { q: conversationsSearch.value || undefined },
    // });
    conversations.value = data;
    if (!selectedConvId.value && conversations.value.length) {
      selectConversation(conversations.value[0].id);
    }
  } catch (e) {
    // handle error UI if desired
  } finally {
    conversationsLoading.value = false;
  }
}

async function fetchMessages(conversationId: number | string, page = 1) {
  if (messagesLoading.value || messagesEnd.value) return;
  messagesLoading.value = true;
  try {
    // DEMO DATA
    const items = demoMessages(conversationId, page);
    // Real API (commented):
    // const { data } = await axios.get(`/api/conversations/${conversationId}/messages`, {
    //   params: { page, per_page: pageSize },
    // });
    // const items: Message[] = Array.isArray(data) ? data : (data?.data || []);

    if (page === 1) {
      messages.value = items.reverse();
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

    if (items.length < pageSize) {
      messagesEnd.value = true;
    }
    messagesPage.value = page;

    // Mark as read (demo no-op)
    // await axios.post(`/api/conversations/${conversationId}/read`);
    const conv = conversations.value.find(c => c.id === conversationId);
    if (conv) conv.unread_count = 0;
  } catch (e) {
    // handle
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
  void fetchMessages(id, 1);
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

function onTyping() {
  composing.typing = true;
  if (typingTimer) clearTimeout(typingTimer);
  typingIndicator.value = true;
  typingTimer = setTimeout(() => {
    typingIndicator.value = false;
    composing.typing = false;
  }, 1200);
}

async function sendMessage() {
  if (!selectedConvId.value || composing.sending) return;
  const text = composing.text.trim();
  const hasFiles = composing.files.length > 0;
  if (!text && !hasFiles) return;

  try {
    composing.sending = true;

    // DEMO: fabricate a new message locally
    const newMsg: Message = {
      id: 'local-' + Date.now(),
      conversation_id: selectedConvId.value,
      sender: me.value!,
      body: text || '(attachment)',
      created_at: new Date().toISOString(),
      attachments: composing.files.map((f, i) => ({ id: i, name: f.name }))
    };

    // Real API (commented):
    // const form = new FormData();
    // form.append('conversation_id', String(selectedConvId.value));
    // if (text) form.append('body', text);
    // composing.files.forEach((f, i) => form.append('attachments[]', f));
    // const { data } = await axios.post('/api/messages', form, {
    //   headers: { 'Content-Type': 'multipart/form-data' },
    // });
    // const newMsg: Message = data;

    messages.value = [...messages.value, newMsg];
    composing.text = '';
    composing.files = [];
    await nextTick();
    scrollToBottom();
  } catch (e) {
    // handle error toast
  } finally {
    composing.sending = false;
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
          <section class="rounded-2xl border border-white/10 bg-[linear-gradient(180deg,#1b2240,#141b33)] shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)] flex flex-col min-h-[60vh]">
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

            <!-- Messages -->
            <div
              ref="listRef"
              class="flex-1 overflow-y-auto px-3 py-4 space-y-2"
              @scroll.passive="onScrollMessages"
            >
              <div v-if="messagesLoading && !messages.length" class="text-[#9AA6D7] text-sm px-2">
                Loading messages…
              </div>

              <div v-if="!messagesLoading && !messages.length && activeConversation" class="text-[#9AA6D7] text-sm px-2">
                Say hello to start the conversation.
              </div>

              <div v-if="messagesLoading && messages.length" class="text-center text-[#9AA6D7] text-xs py-1">
                Loading older messages…
              </div>

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
                    'max-w-[78%] rounded-2xl px-3 py-2 border text-sm leading-relaxed',
                    isMine(m)
                      ? 'bg-[#5B8CFF] text-white border-white/10 rounded-br-md'
                      : 'bg-[#101733] text-white border-white/10 rounded-bl-md'
                  ]"
                >
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
                  <div class="text-[11px] opacity-70 mt-1">
                    {{ fmtTime(m.created_at) }}
                  </div>
                </div>
              </div>

              <div v-if="typingIndicator && activeConversation" class="flex items-center gap-2 text-[#9AA6D7] text-xs px-2 pt-1">
                <span class="inline-flex w-2 h-2 rounded-full bg-[#9AA6D7] animate-pulse"></span>
                Typing…
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

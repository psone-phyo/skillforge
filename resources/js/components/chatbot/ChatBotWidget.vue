<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';

const open = ref(false);
const loading = ref(false);
const sending = ref(false);
const error = ref<string | null>(null);
const messages = ref<Array<{role:'user'|'assistant', text:string, id?:string}>>([]);
const draft = ref('');

function toggle() {
  open.value = !open.value;
  if (open.value && messages.value.length === 0) {
    loadHistory();
  }
}

async function loadHistory() {
  error.value = null;
  loading.value = true;
  try {
    const { data } = await axios.get('/api/chatbot/history');
    const items = data?.items || [];
    messages.value = [];
    items.forEach((it: any) => {
      messages.value.push({ role: 'user', text: it.question, id: it.id + '_q' });
      messages.value.push({ role: 'assistant', text: it.answer, id: it.id + '_a' });
    });
    await nextTick();
    scrollToBottom();
  } catch (e:any) {
    error.value = 'Unable to load history.';
  } finally {
    loading.value = false;
  }
}

function scrollToBottom() {
  const el = document.getElementById('chat-scroll');
  if (el) el.scrollTop = el.scrollHeight;
}

async function send() {
  const text = draft.value.trim();
  if (!text || sending.value) return;
  error.value = null;

  // optimistic UI
  messages.value.push({ role: 'user', text });
  draft.value = '';
  await nextTick(); scrollToBottom();

  try {
    sending.value = true;
    const { data } = await axios.post('/api/chatbot/message', { message: text });
    const reply = data?.reply || '…';
    messages.value.push({ role: 'assistant', text: reply });
    await nextTick(); scrollToBottom();
  } catch (e:any) {
    messages.value.push({ role: 'assistant', text: 'Sorry, I could not process that. Please try again.' });
  } finally {
    sending.value = false;
  }
}
</script>

<template>
  <!-- Floating button -->
  <button
    @click="toggle"
    class="fixed z-50 bottom-5 right-5 h-12 w-12 rounded-full border border-white/15 text-white shadow-[0_10px_30px_rgba(0,0,0,0.35)]"
    style="background:linear-gradient(180deg,#5B8CFF,#7B61FF);"
    aria-label="Chatbot"
  >
    ?
  </button>

  <!-- Panel -->
  <div
    v-if="open"
    class="fixed z-50 bottom-20 right-5 w-[92vw] max-w-sm rounded-2xl border border-white/10 overflow-hidden shadow-[0_10px_30px_rgba(0,0,0,0.35)]"
    style="background:linear-gradient(180deg,#1b2240,#141b33);"
  >
    <div class="px-3 py-2 border-b border-white/10 flex items-center justify-between">
      <div class="text-white font-semibold">SkillForge Assistant</div>
      <button class="text-[#C3CCF3] text-sm" @click="open=false">Close</button>
    </div>

    <div id="chat-scroll" class="max-h-[50vh] overflow-y-auto p-3 space-y-2">
      <div v-if="loading" class="text-[#9AA6D7] text-sm">Loading history…</div>
      <div v-if="error" class="text-pink-200 text-sm">{{ error }}</div>

      <template v-for="(m, idx) in messages" :key="idx">
        <div
          :class="[
            'text-sm px-3 py-2 rounded-xl border',
            m.role === 'user'
              ? 'self-end bg-[#5B8CFF] text-white border-white/10'
              : 'self-start bg-[#101733] text-white border-white/10'
          ]"
          style="max-width: 80%;"
        >
          {{ m.text }}
        </div>
      </template>
    </div>

    <div class="border-t border-white/10 p-2">
      <div class="flex gap-2">
        <input
          v-model="draft"
          type="text"
          :disabled="sending"
          placeholder="Ask a question…"
          class="flex-1 bg-[#0b1024] border border-white/10 rounded-xl px-3 py-2 text-white"
          @keydown.enter.prevent="send"
        />
        <button
          :disabled="sending || !draft.trim()"
          class="px-3 py-2 rounded-lg font-semibold text-white border border-white/20 disabled:opacity-60"
          style="background:linear-gradient(180deg,#5B8CFF,#7B61FF);"
          @click="send"
        >Send</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
#chat-scroll {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
</style>

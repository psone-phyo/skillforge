<script setup lang="ts">
// import '../../css/frontend/style.css';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, computed, onMounted, nextTick } from 'vue';

const props = defineProps<{
  email?: string;         // optionally show where OTP was sent
  id?: number;        // OTP length (default 6)
  data?: string // seconds (default 60)
}>();

const OTP_LENGTH = 6;
const RESEND_COOLDOWN = 60;

// state
const digits = ref<string[]>(Array.from({ length: OTP_LENGTH }, () => ''));
const inputs = ref<HTMLInputElement[]>([]);
const loading = ref(false);
const errorMsg = ref<string | null>(null);
const successMsg = ref<string | null>(null);

// resend
const canResend = ref(false);
const remaining = ref(RESEND_COOLDOWN);
let timer: any = null;

function startTimer() {
  canResend.value = false;
  remaining.value = RESEND_COOLDOWN;
  clearInterval(timer);
  timer = setInterval(() => {
    remaining.value -= 1;
    if (remaining.value <= 0) {
      canResend.value = true;
      clearInterval(timer);
    }
  }, 1000);
}

onMounted(() => {
  startTimer();
  nextTick(() => {
    inputs.value?.[0]?.focus();
  });
});

const code = computed(() => digits.value.join(''));

function onInput(idx: number, e: Event) {
  const input = e.target as HTMLInputElement;
  let val = input.value.replace(/\D+/g, ''); // keep digits only
  if (!val) {
    digits.value[idx] = '';
    return;
  }
  // If user pasted multiple digits into a single box, spread them across
  const chars = val.split('');
  for (let i = 0; i < chars.length && idx + i < OTP_LENGTH; i++) {
    digits.value[idx + i] = chars[i];
  }
  // move focus
  const nextIndex = Math.min(idx + chars.length, OTP_LENGTH - 1);
  inputs.value[nextIndex]?.focus();
}

function onKeydown(idx: number, e: KeyboardEvent) {
  const key = e.key;
  if (key === 'Backspace') {
    if (digits.value[idx]) {
      digits.value[idx] = '';
      return;
    }
    const prev = Math.max(0, idx - 1);
    inputs.value[prev]?.focus();
    digits.value[prev] = '';
    e.preventDefault();
  } else if (key === 'ArrowLeft') {
    inputs.value[Math.max(0, idx - 1)]?.focus();
    e.preventDefault();
  } else if (key === 'ArrowRight') {
    inputs.value[Math.min(OTP_LENGTH - 1, idx + 1)]?.focus();
    e.preventDefault();
  }
}

async function submitOtp() {
  errorMsg.value = null;
  successMsg.value = null;
  const value = code.value;

  if (value.length !== OTP_LENGTH) {
    errorMsg.value = `Please enter the ${OTP_LENGTH}-digit code.`;
    return;
  }

  try {
    loading.value = true;
    const { data } = await axios.post('/verify/otp', { code: value, email: props.email, id:props.id });
    // Handle success (navigate or show message)
    successMsg.value = data?.message || 'Verified successfully.';
    // Optional: redirect if backend instructs
    if (data?.redirect) {
      window.location.href = data.redirect;
    }
  } catch (err: any) {
    console.log(err);

    // Surface server validation or generic error
    const msg =
      err?.response?.data?.message ||
      err?.response?.data?.errors?.code?.[0] ||
      'Verification failed. Please try again.';
    errorMsg.value = msg;
  } finally {
    loading.value = false;
  }
}

async function resendOtp() {
  if (!canResend.value) return;

  try {
    canResend.value = false;
    remaining.value = RESEND_COOLDOWN;
    // Adjust endpoint if different
    await axios.post(`/send/otp/${props.id}`, {email: props.email});
    successMsg.value = 'A new code has been sent.';
    startTimer();
    // reset inputs
    digits.value = Array.from({ length: OTP_LENGTH }, () => '');
    nextTick(() => inputs.value?.[0]?.focus());
  } catch (err: any) {
    canResend.value = true;
    errorMsg.value =
      err?.response?.data?.message ||
      'Could not resend the code. Please try again.';
  }
}
</script>

<template>
  <Head title="Verify OTP" />
  <!-- <AppLayout> -->
    <main class="py-8">
      <div class="max-w-md mx-auto px-4">
        <div
          class="rounded-2xl border border-white/10 p-6 sm:p-7 shadow-[0_10px_30px_rgba(0,0,0,0.35)]"
          style="background:linear-gradient(180deg,#1b2240,#141b33);"
        >
          <h1 class="text-white text-2xl font-extrabold mb-2">Verify OTP</h1>
          <p class="text-[#9AA6D7] text-sm mb-5">
            Enter the {{ OTP_LENGTH }}-digits code we sent
            <span v-if="email">to {{ email }}</span>
          </p>

          <!-- Alerts -->
          <div v-if="errorMsg" class="mb-3 rounded-lg border border-pink-400/40 bg-pink-400/10 px-3 py-2 text-pink-100">
            {{ errorMsg }}
          </div>
          <div v-if="successMsg" class="mb-3 rounded-lg border border-emerald-400/40 bg-emerald-400/10 px-3 py-2 text-emerald-100">
            {{ successMsg }}
          </div>

          <!-- OTP boxes -->
          <div class="flex items-center justify-between gap-2 mb-5">
            <input
              v-for="(_, i) in digits"
              :key="i"
              ref="inputs"
              v-model="digits[i]"
              inputmode="numeric"
              pattern="[0-9]*"
              maxlength="1"
              class="w-12 h-12 text-center text-white text-xl rounded-xl bg-[#0b1024] border border-white/10 focus:outline-none focus:ring-2 focus:ring-[#5B8CFF]"
              @input="onInput(i, $event)"
              @keydown="onKeydown(i, $event)"
            />
          </div>

          <!-- Submit -->
          <button
            :disabled="loading"
            @click="submitOtp"
            class="w-full px-4 py-2 rounded-lg font-semibold text-white border border-white/20 disabled:opacity-60"
            style="background:linear-gradient(180deg,#5B8CFF,#7B61FF);"
          >
            {{ loading ? 'Verifying…' : 'Verify' }}
          </button>

          <!-- Resend -->
          <div class="mt-4 text-center text-[#C3CCF3] text-sm">
            <button
              class="underline disabled:no-underline disabled:opacity-60"
              :disabled="!canResend"
              @click="resendOtp"
            >
              Resend code
            </button>
            <span v-if="!canResend" class="ml-1 opacity-80">
              in {{ remaining }}s
            </span>
          </div>
        </div>
      </div>
    </main>
  <!-- </AppLayout> -->
</template>

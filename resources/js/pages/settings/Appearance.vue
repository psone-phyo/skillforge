<script setup lang="ts">
import { Head, usePage, useForm } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';

const breadcrumbItems: BreadcrumbItem[] = [
  { title: 'Appearance settings', href: edit().url },
];

// Status from backend: 'pending' | 'approved' | 'rejected' | null
const props = defineProps<{ instructor_status?: 'pending' | 'approved' | 'rejected' | null }>();

// Inertia useForm (multipart-ready when any File present)
const form = useForm<{
  proposal: string;
  cv: File | null;
}>({
  proposal: '',
  cv: null,
});

function onFileChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0] || null;
  form.cv = file;
}

function submitInstructor() {
  if (props.instructor_status === 'pending' || props.instructor_status === 'approved') return;

  form.post('/send/proposal', {
    forceFormData: true,         // ensure multipart/form-data
    preserveScroll: true,
    onSuccess: () => {
      // Optional: reset fields after successful submit
      form.reset('proposal', 'cv');
    },
  });
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Appearance settings" />

    <SettingsLayout>
      <div class="space-y-6">
        <HeadingSmall
          title="Appearance settings"
          description="Update your account's appearance settings"
        />
        <AppearanceTabs />

        <!-- Instructor application section -->
        <div
          class="rounded-2xl border border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_10px_30px_rgba(0,0,0,0.35)]"
          style="background:linear-gradient(180deg,#1b2240,#141b33);"
        >
          <h2 class="text-white text-lg font-extrabold">Instructor application</h2>

          <!-- Status banners -->
          <div v-if="props.instructor_status === 'pending'"
               class="rounded-lg border border-yellow-400/40 bg-yellow-400/10 px-3 py-2 text-yellow-100">
            Your request is under review. You cannot submit again at this time.
          </div>

          <div v-else-if="props.instructor_status === 'approved'"
               class="rounded-lg border border-emerald-400/40 bg-emerald-400/10 px-3 py-2 text-emerald-100">
            You are now approved as an instructor.
          </div>

          <div v-else-if="props.instructor_status === 'rejected'"
               class="rounded-lg border border-pink-400/40 bg-pink-400/10 px-3 py-2 text-pink-100">
            Your previous request was rejected. You may submit a new application below.
          </div>

          <!-- Approved: show dashboard CTA -->
          <div v-if="props.instructor_status === 'approved'" class="pt-2">
            <a
              href="/admin"
              class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/20 px-4 py-2 font-semibold text-white hover:bg-white/5"
              style="background:linear-gradient(180deg,#5B8CFF,#7B61FF);"
            >
              Go to Instructor Dashboard
            </a>
          </div>

          <!-- Form (shown when not approved; blocked while pending) -->
          <div v-if="props.instructor_status !== 'approved'" class="space-y-4 pt-2">
            <!-- Proposal -->
            <div class="grid gap-2">
              <label for="proposal" class="text-[#C3CCF3]">Proposal</label>
              <textarea
                id="proposal"
                v-model="form.proposal"
                rows="4"
                :disabled="props.instructor_status === 'pending' || form.processing"
                class="w-full rounded-lg border border-white/10 bg-[#0b1024] px-3 py-2 text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2e3c77] disabled:opacity-60"
                placeholder="Tell us briefly about your expertise and what you plan to teach."
              ></textarea>
              <div v-if="form.errors.proposal" class="text-xs text-pink-200">{{ form.errors.proposal }}</div>
            </div>

            <!-- CV -->
            <div class="grid gap-2">
              <label for="cv" class="text-[#C3CCF3]">Upload CV (PDF or images)</label>
              <input
                id="cv"
                type="file"
                :disabled="props.instructor_status === 'pending' || form.processing"
                accept=".pdf,image/*,application/pdf"
                class="block w-full cursor-pointer rounded-lg border border-white/10 bg-[#0b1024] px-3 py-2 text-white file:mr-4 file:rounded-md file:border-0 file:bg-[#1b2240] file:px-3 file:py-1 file:text-white focus:outline-none focus:ring-2 focus:ring-[#2e3c77] disabled:opacity-60"
                @change="onFileChange"
              />
              <div v-if="form.errors.cv" class="text-xs text-pink-200">{{ form.errors.cv }}</div>
              <div v-if="form.cv" class="text-xs text-[#9AA6D7]">Selected: {{ form.cv.name }}</div>
            </div>

            <!-- Submit -->
            <button
              type="button"
              :disabled="props.instructor_status === 'pending' || form.processing"
              @click="submitInstructor"
              class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/20 px-4 py-2 font-semibold text-white disabled:opacity-60"
              style="background:linear-gradient(180deg,#5B8CFF,#7B61FF);"
            >
              <svg v-if="form.processing" class="h-4 w-4 animate-spin text-white" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
              </svg>
              <span>Submit application</span>
            </button>
          </div>
        </div>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>

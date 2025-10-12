<script setup lang="ts">
import '../../css/frontend/style.css';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import dayjs from 'dayjs';
import { useI18n } from '@/composables/useI18n';

const { t, locale } = useI18n();
const props = defineProps<{
  purchases: Array<{
    id: number | string;
    course_id: number;
    ref: string;
    course_fee?: string;
    total_amount?: string;
    payment_method?: string;
    purchased_at?: string | null;
    course: {
      id: number;
      title: string;
      mm_title: string;
      sub_title?: string | null;
      mm_sub_title?: string | null;
      slug?: string | null;
      course_code?: string | null;
      level?: string | null;
      language?: string | null;
      thumbnail_url?: string | null;
      is_paid?: boolean;
      price?: number | null;
    };
  }>;
  certificates: Array<{
    id: number | string;
    course_id: number;
    issue_number: string;
    issue_date: string;
    certificate_url: string;
    course: {
      id: number;
      title: string;
      mm_title: string;
      slug?: string | null;
      thumbnail_url?: string | null;
      course_code?: string | null;
      level?: string | null;
      language?: string | null;
    };
  }>;
}>();

const breadcrumbs = [
  { title: t('nav.dashboard'), href: dashboard().url },
  { title: t('nav.library') },
];

const tab = ref<'courses' | 'certs'>('courses');

function levelBadgeClasses(lvl?: string | null) {
  switch ((lvl || '').toLowerCase()) {
    case 'basic': return 'border-emerald-400 text-emerald-200 bg-emerald-400/10';
    case 'intermediate': return 'border-yellow-400 text-yellow-200 bg-yellow-400/10';
    case 'advanced': return 'border-pink-400 text-pink-200 bg-pink-400/10';
    default: return 'border-white/10 text-[#C3CCF3] bg-[#0b1024]';
  }
}
function languageBadgeClasses(lang?: string | null) {
  if ((lang || '').toLowerCase() === 'myanmar') return 'border-sky-400 text-sky-200 bg-sky-400/10';
  if ((lang || '').toLowerCase() === 'english') return 'border-violet-400 text-violet-200 bg-violet-400/10';
  return 'border-white/10 text-[#C3CCF3] bg-[#0b1024]';
}

const purchasedCourses = computed(() => props.purchases || []);
const certificates = computed(() => props.certificates || []);
</script>

<template>
  <Head title="My Learning" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <main class="py-8">
      <div class="max-w-6xl mx-auto px-4">
        <!-- Tabs -->
        <div class="rounded-2xl border border-white/10 p-2 mb-6 bg-[linear-gradient(180deg,#1b2240,#141b33)]">
          <div class="flex gap-2">
            <button
              :class="[
                'px-4 py-2 rounded-lg font-semibold border transition',
                tab === 'courses'
                  ? 'text-white border-white/20 bg-[linear-gradient(180deg,#5B8CFF,#7B61FF)]'
                  : 'text-[#C3CCF3] border-white/10 bg-[#0b1024] hover:bg-[#121936]'
              ]"
              @click="tab = 'courses'"
            >
              {{ t('my.purchased_course') }}
            </button>
            <button
              :class="[
                'px-4 py-2 rounded-lg font-semibold border transition',
                tab === 'certs'
                  ? 'text-white border-white/20 bg-sky-700'
                  : 'text-[#C3CCF3] border-white/10 bg-[#0b1024] hover:bg-[#121936]'
              ]"
              @click="tab = 'certs'"
            >
              {{ t('my.certificates') }}
            </button>
          </div>
        </div>

        <!-- Purchased Courses -->
        <section v-if="tab === 'courses'" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="p in purchasedCourses"
            :key="p.id"
            class="rounded-2xl overflow-hidden border border-white/10 bg-[linear-gradient(180deg,#1b2240,#141b33)] shadow-[0_10px_30px_rgba(0,0,0,0.35)]"
          >
            <div class="h-40 bg-center bg-cover" :style="`background-image:url('${p.course.thumbnail_url || ''}')`"></div>
            <div class="p-4">
              <div class="text-white font-extrabold text-lg truncate">{{ locale == 'my' ? p.course.mm_title : p.course.title }}</div>
              <div class="text-[#9AA6D7] text-sm truncate">{{ locale == 'my' ? p.course.mm_sub_title : p.course.sub_title }}</div>
              <div class="flex flex-wrap items-center gap-2 text-xs mt-2">
                <span class="px-2 py-1 rounded-md border" :class="levelBadgeClasses(p.course.level)">{{ t(`courses.level_${p.course.level}`) }}</span>
                <span class="px-2 py-1 rounded-md border" :class="languageBadgeClasses(p.course.language)">{{ t(`courses.language_${p.course.language!.toLowerCase()}`) }}</span>
                <span class="px-2 py-1 rounded-md border border-white/10 text-[#C3CCF3] bg-[#0b1024]">Code: {{ p.course.course_code }}</span>
              </div>

              <div class="mt-3 flex justify-between items-center">
                <span class="text-[#9AA6D7] text-xs">
                  {{ t('my.purchased') }}: {{ p.purchased_at ? dayjs(p.purchased_at).format('MMM D, YYYY') : '—' }}
                </span>
                <a :href="`/course/${p.course.id}`" class="px-3 py-1 rounded-lg font-semibold text-white border border-white/20 hover:bg-[#28306a]">
                  {{ t('my.view') }}
                </a>
              </div>
            </div>
          </div>

          <div v-if="!purchasedCourses.length" class="col-span-full text-center text-[#9AA6D7] py-10">
            {{ t('my.no_purchased_course') }}
          </div>
        </section>

        <!-- Certificates -->
        <section v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="c in certificates"
            :key="c.id"
            class="rounded-2xl overflow-hidden border border-white/10 bg-[linear-gradient(180deg,#1b2240,#141b33)] shadow-[0_10px_30px_rgba(0,0,0,0.35)]"
          >
            <div class="h-40 bg-center bg-cover" :style="`background-image:url('${c.course.thumbnail_url || ''}')`"></div>
            <div class="p-4">
              <div class="text-white font-extrabold text-lg truncate">{{ locale == 'my' ? c.course.mm_title : c.course.title }}</div>
              <div class="text-[#9AA6D7] text-sm truncate">Issue No: {{ c.issue_number }}</div>
              <div class="flex flex-wrap items-center gap-2 text-xs mt-2">
                <span class="px-2 py-1 rounded-md border" :class="levelBadgeClasses(c.course.level)">{{ c.course.level }}</span>
                <span class="px-2 py-1 rounded-md border" :class="languageBadgeClasses(c.course.language)">{{ c.course.language }}</span>
                <span class="px-2 py-1 rounded-md border border-white/10 text-[#C3CCF3] bg-[#0b1024]">
                  {{ t('my.issue') }}: {{ c.issue_date }}
                </span>
              </div>

              <div class="mt-3 flex justify-between items-center">
                <a :href="`/course/${c.course.id}`" class="px-3 py-1 rounded-lg font-semibold text-white border border-white/20 hover:bg-[#28306a]">
                  {{ t('my.course') }}
                </a>
                <a :href="c.certificate_url+c.id" target="_blank" rel="noopener" class="px-3 py-1 rounded-lg font-semibold text-white border border-white/20 bg-sky-500 hover:bg-sky-700">
                    {{ t('my.certificate') }}
                </a>
              </div>
            </div>
          </div>

          <div v-if="!certificates.length" class="col-span-full text-center text-[#9AA6D7] py-10">
            {{ t('my.no_certificates') }}
          </div>
        </section>
      </div>
    </main>
  </AppLayout>
</template>

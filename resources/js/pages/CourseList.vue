<script setup lang="ts">
import '../../css/frontend/style.css';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import dayjs from 'dayjs';
import { useI18n } from '@/composables/useI18n';

const { t, locale } = useI18n();
const props = defineProps<{
  courses: {
    data: any[];
    meta?: any;
    links?: any[];
  };
  categories: Array<{ id: number; name: string; slug: string }>;
  tags: Array<{ id: number; name: string; slug: string }>;
  filters: {
    q?: string;
    category?: number | string | null;
    tag?: number | string | null;
    level?: string | null;
    language?: string | null;
    is_paid?: string | number | null;
    status?: string | null;
    price_min?: string | number | null;
    price_max?: string | number | null;
    sort?: string | null;
  };
}>();

const breadcrumbs = [
  { title: t('nav.home'), href: dashboard().url },
  { title: t('nav.courses') },
];

// State
const q = ref(props.filters.q || '');
const category = ref(props.filters.category || '');
const tag = ref(props.filters.tag || '');
const level = ref(props.filters.level || '');
const language = ref(props.filters.language || '');
const is_paid = ref(props.filters.is_paid ?? '');
const status = ref(props.filters.status || '');
const price_min = ref(props.filters.price_min ?? '');
const price_max = ref(props.filters.price_max ?? '');
const sort = ref(props.filters.sort || 'newest');

const courseItems = computed(() => props.courses?.data || []);

// Actions

// Search only by title/code (q) — keeps other filters as is
function searchTitleCode() {
  router.get('/courses', {
    q: q.value || undefined,
    // preserve other filters if they already exist
    // category: category.value || undefined,
    // tag: tag.value || undefined,
    // level: level.value || undefined,
    // language: language.value || undefined,
    // is_paid: is_paid.value === '' ? undefined : is_paid.value,
    // status: status.value || undefined,
    // price_min: price_min.value || undefined,
    // price_max: price_max.value || undefined,
    // sort: sort.value || undefined,
  }, {
    preserveScroll: true,
    replace: true,
  });
}

// Apply filters (category/tag/level/language/price/etc.) — does not change q
function applyFilters() {
  router.get('/courses', {
    q: q.value || undefined,
    category: category.value || undefined,
    tag: tag.value || undefined,
    level: level.value || undefined,
    language: language.value || undefined,
    is_paid: is_paid.value === '' ? undefined : is_paid.value,
    price_min: price_min.value || undefined,
    price_max: price_max.value || undefined,
    sort: sort.value || undefined,
  }, {
    preserveScroll: true,
    replace: true,
  });
}

function clearFilters() {
  category.value = '';
  tag.value = '';
  level.value = '';
  language.value = '';
  is_paid.value = '';
  price_min.value = '';
  price_max.value = '';
  sort.value = 'newest';
  applyFilters();
}

// Badge helpers
function levelBadgeClasses(lvl: string) {
  switch ((lvl || '').toLowerCase()) {
    case 'basic':
      return 'border-emerald-400 text-emerald-200 bg-emerald-400/10';
    case 'intermediate':
      return 'border-yellow-400 text-yellow-200 bg-yellow-400/10';
    case 'advanced':
      return 'border-pink-400 text-pink-200 bg-pink-400/10';
    default:
      return 'border-white/10 text-[#C3CCF3] bg-[#0b1024]';
  }
}
function priceBadgeClasses(isPaid: boolean) {
  return isPaid
    ? 'border-indigo-400 text-indigo-200 bg-indigo-400/10'
    : 'border-emerald-400 text-emerald-200 bg-emerald-400/10';
}
function languageBadgeClasses(lang: string) {
  // Pick distinct outlines per language
  if ((lang || '').toLowerCase() === 'myanmar') {
    return 'border-sky-400 text-sky-200 bg-sky-400/10';
  }
  if ((lang || '').toLowerCase() === 'english') {
    return 'border-violet-400 text-violet-200 bg-violet-400/10';
  }
  return 'border-white/10 text-[#C3CCF3] bg-[#0b1024]';
}
function ratingBadgeClasses() {
  return 'border-amber-400 text-amber-200 bg-amber-400/10';
}
</script>

<template>
  <Head :title="t('courses.page_title')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <main class="py-8">
      <div class="max-w-6xl mx-auto px-4">

        <!-- Top: Search -->
        <section class="rounded-2xl border border-white/10 p-4 mb-4 bg-[linear-gradient(180deg,#1b2240,#141b33)]">
          <div class="flex flex-col gap-3 md:items-center">
            <div class="flex-1 flex gap-2 w-full">
              <input
                v-model="q"
                @keydown.enter="searchTitleCode"
                :placeholder="t('courses.search_placeholder')"
                class="flex-1 bg-[#0b1024] border border-white/10 rounded-xl px-3 py-2 text-white placeholder:text-[#9AA6D7]"
              />
              <button
                class="px-4 py-2 rounded-lg font-semibold text-white border border-white/20"
                style="background:linear-gradient(180deg,#5B8CFF,#7B61FF);"
                @click="searchTitleCode"
                :title="t('courses.search')"
              >
                {{ t('courses.search') }}
              </button>
            </div>
          </div>
        </section>

        <!-- Filters -->
        <section class="rounded-2xl border border-white/10 p-4 mb-6 bg-[linear-gradient(180deg,#1b2240,#141b33)]">
          <div class="grid gap-3 md:grid-cols-[1fr_1fr_1fr_1fr]">
            <select v-model="category" class="bg-[#0b1024] border border-white/10 rounded-xl px-3 py-2 text-white">
              <option value="">{{ t('courses.all_categories') }}</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>

            <select v-model="tag" class="bg-[#0b1024] border border-white/10 rounded-xl px-3 py-2 text-white">
              <option value="">{{ t('courses.all_tags') }}</option>
              <option v-for="t in tags" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>

            <select v-model="level" class="bg-[#0b1024] border border-white/10 rounded-xl px-3 py-2 text-white">
              <option value="">{{ t('courses.all_levels') }}</option>
              <option value="basic">{{ t('courses.level_basic') }}</option>
              <option value="intermediate">{{ t('courses.level_intermediate') }}</option>
              <option value="advanced">{{ t('courses.level_advanced') }}</option>
            </select>

            <select v-model="language" class="bg-[#0b1024] border border-white/10 rounded-xl px-3 py-2 text-white">
              <option value="">{{ t('courses.all_languages') }}</option>
              <option value="Myanmar">{{ t('courses.language_myanmar') }}</option>
              <option value="English">{{ t('courses.language_english') }}</option>
            </select>
          </div>

          <div class="grid gap-3 mt-3 md:grid-cols-[1fr_1fr_1fr_1fr]">
            <select v-model="is_paid" class="bg-[#0b1024] border border-white/10 rounded-xl px-3 py-2 text-white">
              <option value="">{{ t('courses.all_price_types') }}</option>
              <option value="1">{{ t('courses.paid') }}</option>
              <option value="0">{{ t('courses.free') }}</option>
            </select>

            <input
              v-model="price_min"
              type="number"
              min="0"
              :placeholder="t('courses.min_price')"
              class="bg-[#0b1024] border border-white/10 rounded-xl px-3 py-2 text-white"
            />
            <input
              v-model="price_max"
              type="number"
              min="0"
              :placeholder="t('courses.max_price')"
              class="bg-[#0b1024] border border-white/10 rounded-xl px-3 py-2 text-white"
            />
            <select v-model="sort" class="bg-[#0b1024] border border-white/10 rounded-xl px-3 py-2 text-white">
              <option value="newest">{{ t('courses.sort_newest') }}</option>
              <option value="price_asc">{{ t('courses.sort_price_asc') }}</option>
              <option value="price_desc">{{ t('courses.sort_price_desc') }}</option>
              <option value="rating_desc">{{ t('courses.sort_rating_desc') }}</option>
            </select>
          </div>

          <div class="my-2 flex gap-2 justify-end">
            <button
              class="px-4 py-2 rounded-lg font-semibold text-white border border-white/20 bg-sky-400"
              @click="applyFilters"
            >
              {{ t('courses.apply_filter') }}
            </button>
            <button
              class="px-4 py-2 rounded-lg font-semibold text-white border border-white/20"
              style="background:linear-gradient(180deg,#1b2240,#141b33);"
              @click="clearFilters"
            >
              {{ t('courses.clear') }}
            </button>
          </div>
        </section>

        <!-- Courses grid -->
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="c in courseItems"
            :key="c.id"
            class="rounded-2xl overflow-hidden border border-white/10 bg-[linear-gradient(180deg,#1b2240,#141b33)] shadow-[0_10px_30px_rgba(0,0,0,0.35)]"
          >
            <div class="h-40 bg-center bg-cover" :style="`background-image:url('${c.thumbnail_url || ''}')`"></div>
            <div class="p-4">
              <div class="text-white font-extrabold text-lg truncate">{{ c.title }}</div>
              <div class="text-[#9AA6D7] text-sm truncate">{{ c.sub_title }}</div>

              <!-- Badges -->
              <div class="flex flex-wrap items-center gap-2 text-xs mt-2">
                <span class="px-2 py-1 rounded-md border" :class="levelBadgeClasses(c.level)">{{ t(`courses.level_${c.level}`) || c.level }}</span>
                <span class="px-2 py-1 rounded-md border" :class="languageBadgeClasses(c.language)">{{ t(`courses.language_${c.language.toLowerCase()}`) || c.language }}</span>
                <span v-if="c.rating_avg" class="px-2 py-1 rounded-md border" :class="ratingBadgeClasses()">★ {{ Number(c.rating_avg).toFixed(1) }}</span>
                <span class="px-2 py-1 rounded-md border" :class="priceBadgeClasses(!!c.is_paid)">
                  {{ c.is_paid ? (c.price ? c.price + ' MMK' : t('courses.paid')) : t('courses.free') }}
                </span>
              </div>

              <!-- Categories/Tags -->
              <div class="flex flex-wrap gap-2 mt-2">
                <span
                  v-for="cat in (c.categories || [])"
                  :key="cat.id"
                  class="px-2 py-1 rounded-full text-xs border border-white/10 text-[#C3CCF3] bg-[#1C2340]"
                >{{ cat.name }}</span>
                <span
                  v-for="t in (c.tags || [])"
                  :key="t.id"
                  class="px-2 py-1 rounded-full text-xs border border-white/10 text-[#C3CCF3] bg-[#1C2340]"
                >#{{ t.name }}</span>
              </div>

              <div class="mt-3 flex justify-between items-center">
                <span class="text-[#9AA6D7] text-xs">Code: {{ c.course_code }}</span>
                <a :href="`/course/${c.id}`" class="px-3 py-1 rounded-lg font-semibold text-white border border-white/20 hover:bg-[#28306a]">
                  {{ t('courses.view') }}
                </a>
              </div>
            </div>
          </div>
        </section>

        <!-- Pagination -->
        <nav class="mt-6 flex items-center justify-center gap-2">
          <a
            v-for="link in props.courses.links || []"
            :key="link.url || link.label"
            :href="link.url || '#'"
            v-html="link.label"
            class="px-3 py-1 rounded-lg border border-white/10 text-[#C3CCF3]"
            :class="{ 'bg-[#101733] text-white': link.active, 'pointer-events-none opacity-50': !link.url }"
          ></a>
        </nav>
      </div>
    </main>
  </AppLayout>
</template>


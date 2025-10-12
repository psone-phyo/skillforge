<script setup>
import '../../css/frontend/style.css';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head } from '@inertiajs/vue3';
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";
import customParseFormat from 'dayjs/plugin/customParseFormat';
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3'
import { useI18n } from '@/composables/useI18n';

const page = usePage()
const { t, locale } = useI18n();

dayjs.extend(relativeTime)
dayjs.extend(customParseFormat)

const props = defineProps({
    categories: Array,
    courses: Array,
    fileUrl: String,
});

const track = ref(null);

const scrollBy = (direction) => {
    const el = track.value;
    if (!el) return;
    const amount = el.clientWidth * direction;
    el.scrollBy({ left: amount, behavior: 'smooth' });
};
</script>

<template>
  <Head :title="t('dashboard.page_title')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Hero -->
    <section class="py-12 px-1">
      <div class="max-w-6xl mx-auto grid gap-6 md:grid-cols-[1.2fr_.8fr]">
        <!-- Left -->
        <div class="rounded-2xl border border-white/5 shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)] p-6 md:p-7"
             style="background:linear-gradient(180deg, rgba(27,34,64,0.9), rgba(20,27,51,0.9));">
          <p class="text-[#42E3B4] font-extrabold tracking-wide mb-2">{{ t('dashboard.hero.kicker') }}</p>
          <h1 class="text-white text-3xl sm:text-4xl font-extrabold leading-tight mb-2">
            {{ t('dashboard.hero.title') }}
          </h1>
          <p class="text-[#8F9BB3] mb-4">
            {{ t('dashboard.hero.subtitle') }}
          </p>

          <!-- Search -->
          <div class="flex items-center gap-2 bg-[#0f152c] border border-white/10 rounded-xl p-2">
            <svg width="18" height="18" fill="#A6B3E8" viewBox="0 0 24 24" aria-hidden="true" class="shrink-0">
              <path d="M15.5 14h-.79l-.28-.27a6.471 6.471 0 0 0 1.57-4.23 6.5 6.5 0 1 0-6.5 6.5 6.471 6.471 0 0 0 4.23-1.57l.27.28v.79l5 5L20.49 19l-5-5Zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14Z"/>
            </svg>
            <input
              id="searchInput"
              class="flex-1 bg-transparent outline-none text-white placeholder:text-[#AAB3D0] text-[15px]"
              :placeholder="t('dashboard.search.placeholder')"
            />
            <span class="hidden sm:inline bg-[#0b1024] border border-white/10 text-[#AAB3D0] px-2 py-1 rounded-md text-xs">/</span>
            <button class="px-3 py-2 rounded-lg font-semibold border border-white/10 text-white transition
                           hover:-translate-y-px
                           shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)]"
                    style="background:linear-gradient(180deg, #1b2240, #141b33);">
              {{ t('dashboard.search.button') }}
            </button>
          </div>

          <!-- Chips -->
          <div class="flex flex-wrap gap-2 mt-3">
            <div class="px-3 py-2 rounded-full font-semibold text-[13px] cursor-pointer transition
                        border border-white/10 text-white
                        bg-gradient-to-b from-[#1f2958] to-[#182145]">
              {{ t('dashboard.chips.all') }}
            </div>
            <div
              v-for="category in props.categories"
              :key="category.id"
              class="px-3 py-2 rounded-full font-semibold text-[13px] cursor-pointer transition
                     border border-white/10 text-[#C3CCF3] bg-[#1C2340]
                     hover:text-white hover:bg-gradient-to-b hover:from-[#1f2958] hover:to-[#182145]"
            >
              {{ category.name }}
            </div>
          </div>
        </div>

        <!-- Right -->
        <div class="grid gap-3 content-start">
          <!-- Stats -->
          <div class="grid grid-cols-3 gap-3">
            <div class="rounded-xl text-center p-3 border border-white/10
                        shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)]"
                 style="background:linear-gradient(180deg, #1b2240, #141b33);">
              <small class="block text-[#8F9BB3]">{{ t('dashboard.stats.courses') }}</small>
              <b class="block text-lg text-white">1,248</b>
            </div>
            <div class="rounded-xl text-center p-3 border border-white/10
                        shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)]"
                 style="background:linear-gradient(180deg, #1b2240, #141b33);">
              <small class="block text-[#8F9BB3]">{{ t('dashboard.stats.videos') }}</small>
              <b class="block text-lg text-white">15,930</b>
            </div>
            <div class="rounded-xl text-center p-3 border border-white/10
                        shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)]"
                 style="background:linear-gradient(180deg, #1b2240, #141b33);">
              <small class="block text-[#8F9BB3]">{{ t('dashboard.stats.learners') }}</small>
              <b class="block text-lg text-white">72k+</b>
            </div>
          </div>

          <!-- Preview -->
          <div class="grid grid-cols-1 sm:grid-cols-[100px_1fr] gap-3 p-4 rounded-xl border border-white/10
                      items-center
                      shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)]"
               style="background:linear-gradient(180deg, #1b2240, #141b33);">
            <div class="h-[68px] rounded-lg border border-white/10 bg-[url('https://images.unsplash.com/photo-1518779578993-ec3579fee39f?q=80&w=600&auto=format&fit=crop')] bg-center bg-cover"></div>
            <div>
              <h3 class="text-white font-semibold">{{ t('dashboard.preview.title') }}</h3>
              <p class="text-[#8F9BB3] text-sm">{{ t('dashboard.preview.subtitle') }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Videos: slider -->
    <section class="pb-24 px-4">
      <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-white text-xl font-bold">{{ t('dashboard.trending.title') }}</h2>
          <a href="/courses" class="text-[#AFC7FF] font-semibold">{{ t('dashboard.trending.view_all') }}</a>
        </div>

        <div class="grid grid-cols-[auto_1fr_auto] items-center gap-2">
          <!-- Prev -->
          <button
            class="hidden sm:block w-9 h-9 rounded-lg grid place-items-center cursor-pointer
                   border border-white/15 text-white
                   hover:shadow-[0_6px_16px_rgba(0,0,0,0.25)]"
            style="background:linear-gradient(180deg, #1b2240, #141b33);"
            @click="scrollBy(-1)"
            aria-label="Previous"
          >‹</button>

          <!-- Track -->
          <div
            ref="track"
            class="overflow-x-auto snap-x snap-mandatory flex gap-4 pb-2 [-ms-overflow-style:none] [scrollbar-width:none]"
          >
            <div
              v-for="course in courses"
              :key="course.id"
              class="snap-start min-w-[260px] w-[calc(25%-12px)] md:w-[calc(33.333%-10px)] sm:w-[calc(50%-8px)] xs:w-full"
            >
              <a :href="`/course/${course.id}`" class="block">
                <div
                  class="rounded-2xl overflow-hidden border border-white/10
                         shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)]
                         flex flex-col min-h-[220px]"
                  style="background:linear-gradient(180deg, #1b2240, #141b33);"
                >
                  <div class="relative h-[140px] bg-[#0d1328]">
                    <img
                      :src="fileUrl + course.thumbnail_url"
                      alt="Course thumbnail"
                      class="w-full h-full object-cover"
                    />
                    <div class="absolute left-2 top-2 px-2 py-1 rounded-full text-xs border border-white/20
                                bg-black/50 backdrop-blur text-[#D9E2FF]">
                      {{ t('dashboard.trending.badge_category') }}
                    </div>
                  </div>

                  <div class="p-3 flex gap-2">
                    <div class="w-[34px] h-[34px] rounded-lg border border-white/10 bg-[#0b1024] shrink-0"></div>
                    <div class="flex-1">
                      <div class="text-white font-bold text-sm leading-snug line-clamp-2">
                        {{ locale == 'my' ? course.mm_title : course.title}}
                      </div>
                      <div class="text-[#9AA6D7] text-xs flex items-center gap-2">
                        <span>{{ dayjs(course.published_at).fromNow() }}</span>
                        <span class="inline-block w-1 h-1 bg-[#42508a] rounded-full"></span>
                        <span>{{ course.instructor.name }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          </div>

          <!-- Next -->
          <button
            class="hidden sm:block w-9 h-9 rounded-lg grid place-items-center cursor-pointer
                   border border-white/15 text-white
                   hover:shadow-[0_6px_16px_rgba(0,0,0,0.25)]"
            style="background:linear-gradient(180deg, #1b2240, #141b33);"
            @click="scrollBy(1)"
            aria-label="Next"
          >›</button>
        </div>

        <div class="h-28"></div>
      </div>
    </section>
  </AppLayout>
</template>

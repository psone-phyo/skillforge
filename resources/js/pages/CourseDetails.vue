<script setup lang="ts">
import '../../css/frontend/course-details.css';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head, useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import customParseFormat from 'dayjs/plugin/customParseFormat';
import { ref, onMounted, watch, watchEffect } from 'vue';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const successMessage = ref('');
const toastMessage = ref('')
const toastVisible = ref(false)

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            showToast(flash.success)
        } else if (flash?.error) {
            showToast(flash.error)
        }
    },
    { deep: true, immediate: true }
)
dayjs.extend(relativeTime);
dayjs.extend(customParseFormat);

const breadcrumbs = [{ title: 'Dashboard', href: dashboard().url }];

const props = defineProps<{
    course: any;      // { id, title, tags?, lessons: [{ id, title, video_url, is_locked?, duration?, duration_sec? }] }
    fileUrl: string;  // prefix for relative video URLs
    courseStatus: String;
}>();

// Player state
const playerEl = ref<HTMLIFrameElement | null>(null);
const currentVideoSrc = ref<string>('');
const lessonTitle = ref('');
const isLocked = ref(true);
const currentLesson = ref(0);

// Build absolute video src (prefix with fileUrl if relative)
function buildSrc(url: string): string {
    if (!url) return '';
    if (/^https?:\/\//i.test(url)) return url;
    return `${props.fileUrl || ''}${url}`;
}

// Toast
const toastEl = ref<HTMLElement | null>(null);
function showToast(message: string) {
    toastMessage.value = message
    toastVisible.value = true
    setTimeout(() => {
        toastVisible.value = false
    }, 3000)
}

// Stars
const courseStarsEl = ref<HTMLElement | null>(null);
const summaryStarsEl = ref<HTMLElement | null>(null);
const reviewsEl = ref<HTMLElement | null>(null);
const DEFAULT_RATING = 4.7;
const ratingAvg = ref<number>(DEFAULT_RATING);
function renderStars(el: HTMLElement | null, value: number) {
    if (!el) return;
    el.innerHTML = '';
    const rounded = Math.round(value);
    for (let i = 1; i <= 5; i++) {
        const filled = i <= rounded;
        el.innerHTML += `<svg width="16" height="16" viewBox="0 0 24 24" fill="${filled ? '#FFD36E' : '#2A3159'}" stroke="none"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>`;
    }
}

// Duration helpers
function formatSeconds(total: number): string {
    const sec = Math.max(0, Math.round(total));
    const h = Math.floor(sec / 3600);
    const m = Math.floor((sec % 3600) / 60);
    const s = sec % 60;
    if (h > 0) return `${String(h)}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    return `${String(m)}:${String(s).padStart(2, '0')}`;
}

// Probe duration in browser using <video preload="metadata">
async function probeDurationSeconds(url: string): Promise<number | null> {
    return new Promise((resolve) => {
        try {
            const v = document.createElement('video');
            v.preload = 'metadata';
            v.src = url;
            const cleanup = () => {
                v.src = '';
                v.load();
            };
            v.onloadedmetadata = () => {
                const d = Number(v.duration);
                cleanup();
                if (!isFinite(d) || d <= 0) return resolve(null);
                resolve(Math.round(d));
            };
            v.onerror = () => {
                cleanup();
                resolve(null);
            };
        } catch {
            resolve(null);
        }
    });
}

// Store duration strings per-lesson (keyed by id or index)
const lessonDurations = ref<Record<string | number, string>>({});
function getLessonKey(lesson: any, idx: number) { return lesson?.id ?? idx; }
async function ensureLessonDuration(lesson: any, idx: number) {
    const key = getLessonKey(lesson, idx);
    if (lesson?.duration && typeof lesson.duration === 'string') { lessonDurations.value[key] = lesson.duration; return; }
    if (typeof lesson?.duration_sec === 'number' && lesson.duration_sec > 0) {
        lessonDurations.value[key] = formatSeconds(lesson.duration_sec); return;
    }
    const src = buildSrc(lesson?.video_url || '');
    if (!src) return;
    lessonDurations.value[key] = '...';
    const probed = await probeDurationSeconds(src);
    if (probed && probed > 0) lessonDurations.value[key] = formatSeconds(probed);
    else delete lessonDurations.value[key];
}

// Handle clicking a lesson
function playLesson(lesson: any, index: number) {
    currentVideoSrc.value = buildSrc(lesson.video_url || '');
    isLocked.value = !!lesson.is_locked;
    lessonTitle.value = lesson.title;
    currentLesson.value = index;
}

// Reviews (demo)
const ratingSelect = ref<number>(5);
const reviewText = ref<string>('');
function submitReview() {
    const stars = ratingSelect.value;
    const text = reviewText.value.trim();
    if (!text || !reviewsEl.value) return;
    const node = document.createElement('div');
    node.className = 'review';
    node.innerHTML = `
    <div class="r-avatar" style="background:#0b1024"></div>
    <div>
      <div class="r-name">You</div>
      <div class="r-text"></div>
    </div>
    <div class="r-stars"></div>
  `;
    node.querySelector('.r-text')!.textContent = text;
    const s = document.createElement('span');
    (node.querySelector('.r-stars') as HTMLElement).appendChild(s);
    renderStars(s, stars);
    reviewsEl.value!.prepend(node);
    const current = ratingAvg.value || DEFAULT_RATING;
    const newAvg = Math.min(5, Math.round(((current + stars) / 2) * 10) / 10);
    ratingAvg.value = Number(newAvg.toFixed(1));
    renderStars(summaryStarsEl.value, ratingAvg.value);
    reviewText.value = ''; ratingSelect.value = 5;
    showToast('Thanks for your review!');
}

// Chat (minimal)
const chatOpen = ref(false);
function toggleChat() { chatOpen.value = !chatOpen.value; }
function minimizeChat() { chatOpen.value = false; }

// Buy modal + upload form (Inertia multipart)
const buyModalOpen = ref(false);
const qrImageUrl = ref<string>('/qr-placeholder.png'); // replace with your real QR image path

const form = useForm({
    course_id: null as number | null,
    proof: null as File | null,
    note: '' as string,
    fee: 0,
});

watch(buyModalOpen, (open) => {
    if (open) {
        form.course_id = props.course?.id ?? null;
    } else {
        form.proof = null;
        form.note = '';
        form.fee = 0;
    }
});

function openBuyModal() {
    buyModalOpen.value = true;
}
function closeBuyModal() {
    buyModalOpen.value = false;
}

// Submit to your controller. Adjust URL to your actual route.
function submitPayment() {
    if (!form.course_id) form.course_id = props.course?.id ?? null;
        if (!form.fee || form.fee === 0) form.fee = props.course?.price ?? 0;
    form.post(`/course/${form.course_id}/payments`, {
        forceFormData: true,
        onSuccess: () => {
            closeBuyModal();
            showToast('Upload received. We will review your payment.');
        },
        onError: () => {
            showToast('There was an error submitting your payment.');
        },
    });
}

// file input handlers
function onFileChange(e: Event) {
    const files = (e.target as HTMLInputElement).files;
    form.proof = files && files[0] ? files[0] : null;
}

onMounted(async () => {
    const first = props.course?.lessons?.[0];
    isLocked.value = !!first?.is_locked;
    currentVideoSrc.value = buildSrc(first?.video_url || '');
    lessonTitle.value = first?.title || '';
    currentLesson.value = 0;

    renderStars(courseStarsEl.value, DEFAULT_RATING);
    renderStars(summaryStarsEl.value, DEFAULT_RATING);
    document.querySelectorAll<HTMLElement>('.r-stars[data-stars]').forEach((el) => {
        const v = Number(el.dataset.stars || '0');
        renderStars(el, v);
    });

    // Precompute durations
    const lessons: any[] = props.course?.lessons ?? [];
    for (let i = 0; i < lessons.length; i++) void ensureLessonDuration(lessons[i], i);
});
</script>

<template>

    <Head title="Course" />

    <AppLayout :breadcrumbs="breadcrumbs">
          <Transition name="fade">
    <div
      v-if="toastVisible"
      class="fixed top-5 right-5 px-4 py-3 rounded-lg text-white bg-green-600 shadow-lg z-50"
    >
      {{ toastMessage }}
    </div>
  </Transition>
        <main class="py-8">
            <div class="max-w-6xl mx-auto px-4">
                <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
                    <div class="space-y-4">
                        <!-- Player -->
                        <section
                            class="rounded-2xl overflow-hidden border border-white/10 shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)]"
                            style="background:linear-gradient(180deg,#1b2240,#141b33);">
                            <div
                                class="relative w-full aspect-video bg-[#0b1024] border-b border-white/10 overflow-hidden">
                                <img v-if="isLocked" src="/lock.png" title="Course video"
                                    class="absolute inset-0 w-full h-full bg-sky-900" style="object-fit: contain;">
                                <iframe v-else ref="playerEl" :src="currentVideoSrc"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen title="Course video"
                                    class="absolute inset-0 w-full h-full"></iframe>
                            </div>

                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3 p-4">
                                <div>
                                    <h1 class="text-white text-xl md:text-2xl font-extrabold">{{ lessonTitle }}</h1>
                                    <h3 class="text-[#9AA6D7] text-md md:text-lg font-semibold">{{ course.title ||
                                        'Course' }}</h3>
                                    <div class="flex flex-wrap items-center gap-2 text-[#9AA6D7] text-sm mt-1">
                                        <span ref="courseStarsEl" class="inline-flex"></span>
                                        <span>{{ ratingAvg.toFixed(1) }} (1,248 ratings)</span>
                                        <span>•</span>
                                        <span>{{ course?.lessons?.length || 0 }} lessons</span>
                                    </div>
                                    <div class="flex flex-wrap gap-2 mt-2" v-if="course?.tags?.length">
                                        <span v-for="tag in course.tags" :key="tag.id ?? tag.name"
                                            class="px-3 py-1 rounded-full text-xs border border-white/10 text-[#C3CCF3] bg-[#1C2340]">{{
                                            tag.name }}</span>
                                    </div>
                                </div>
                                <div class="flex gap-2"></div>
                            </div>
                        </section>

                        <!-- Curriculum -->
                        <section
                            class="rounded-2xl overflow-hidden border border-white/10 shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)]"
                            style="background:linear-gradient(180deg,#1b2240,#141b33);">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
                                <div class="font-extrabold text-white">Course Curriculum</div>
                                <div class="text-[#9AA6D7] text-sm">Preview the first 2 lessons for free</div>
                            </div>

                            <div class="divide-y divide-white/5">
                                <div v-for="(lesson, idx) in course.lessons" :key="lesson.id ?? idx"
                                    class="flex items-center gap-3 px-4 py-3 hover:bg-[#121936] cursor-pointer"
                                    :class="{ 'border border-sky-500 rounded-md': currentLesson === idx }"
                                    @click="playLesson(lesson, idx)">
                                    <div
                                        class="w-9 h-9 flex items-center justify-center rounded-lg border border-white/10 bg-[#0b1024] text-[#A7B2DB] font-bold">
                                        {{ idx + 1 }}
                                    </div>

                                    <div class="flex-1">
                                        <div class="text-white font-semibold">{{ lesson.title || `Lesson ${idx + 1}` }}
                                        </div>
                                        <div class="text-[#9AA6D7] text-xs">
                                            <template v-if="lesson.duration && typeof lesson.duration === 'string'">
                                                {{ lesson.duration }}
                                            </template>
                                            <template
                                                v-else-if="typeof lesson.duration_sec === 'number' && lesson.duration_sec > 0">
                                                {{ Math.floor(lesson.duration_sec / 3600) > 0
                                                    ? Math.floor(lesson.duration_sec / 3600) + ':' +
                                                    String(Math.floor((lesson.duration_sec % 3600) / 60)).padStart(2, '0') +
                                                    ':' + String(Math.floor(lesson.duration_sec % 60)).padStart(2, '0')
                                                    : Math.floor((lesson.duration_sec || 0) / 60) + ':' +
                                                String(Math.floor((lesson.duration_sec || 0) % 60)).padStart(2,'0')
                                                }}
                                            </template>
                                            <template v-else>
                                                {{ lessonDurations[lesson.id ?? idx] || '' }}
                                            </template>
                                        </div>
                                    </div>

                                    <span v-if="!lesson.is_locked && courseStatus && courseStatus == 'approved'"
                                        class="px-2 py-1 rounded-md text-xs border border-green-300/40 text-green-200 bg-green-300/10">Unlocked</span>
                                    <span v-else-if="!lesson.is_locked"
                                        class="px-2 py-1 rounded-md text-xs border border-emerald-300/40 text-emerald-200 bg-emerald-300/10">Free
                                        preview</span>
                                    <span v-else
                                        class="px-2 py-1 rounded-md text-xs border border-pink-300/40 text-pink-200 bg-pink-300/10">Locked</span>
                                </div>
                            </div>
                        </section>

                        <!-- Reviews (unchanged aside from layout) -->
                        <section
                            class="rounded-2xl overflow-hidden border border-white/10 shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)]"
                            style="background:linear-gradient(180deg,#1b2240,#141b33);">
                            <div class="flex items-center gap-4 px-4 py-3 border-b border-white/10">
                                <div class="text-4xl font-extrabold text-white">{{ ratingAvg.toFixed(1) }}</div>
                                <div ref="summaryStarsEl" class="inline-flex"></div>
                                <div class="flex-1 grid gap-1">
                                    <div class="h-2 rounded-full border border-white/10 overflow-hidden bg-[#0b1024]">
                                        <span class="block h-full bg-gradient-to-r from-emerald-400 to-blue-400"
                                            style="width:78%"></span>
                                    </div>
                                    <div class="h-2 rounded-full border border-white/10 overflow-hidden bg-[#0b1024]">
                                        <span class="block h-full bg-gradient-to-r from-emerald-400 to-blue-400"
                                            style="width:62%"></span>
                                    </div>
                                    <div class="h-2 rounded-full border border-white/10 overflow-hidden bg-[#0b1024]">
                                        <span class="block h-full bg-gradient-to-r from-emerald-400 to-blue-400"
                                            style="width:28%"></span>
                                    </div>
                                </div>
                            </div>

                            <div ref="reviewsEl" class="px-4 py-2 divide-y divide-white/10">
                                <div class="grid grid-cols-[auto_1fr_auto] gap-3 py-3">
                                    <div
                                        class="w-9 h-9 rounded-full border border-white/10 bg-[url('https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=300&auto=format&fit=crop')] bg-center bg-cover">
                                    </div>
                                    <div>
                                        <div class="text-white font-semibold">Jordan P.</div>
                                        <div class="text-[#C5CFEE] text-sm">Excellent explanations and practical
                                            patterns. The free previews
                                            sold me.</div>
                                    </div>
                                    <div class="r-stars" data-stars="5"></div>
                                </div>
                                <div class="grid grid-cols-[auto_1fr_auto] gap-3 py-3">
                                    <div
                                        class="w-9 h-9 rounded-full border border-white/10 bg-[url('https://images.unsplash.com/photo-1502685104226-ee32379fefbe?q=80&w=300&auto=format&fit=crop')] bg-center bg-cover">
                                    </div>
                                    <div>
                                        <div class="text-white font-semibold">Sofia L.</div>
                                        <div class="text-[#C5CFEE] text-sm">Up-to-date and concise—loved the performance
                                            section.</div>
                                    </div>
                                    <div class="r-stars" data-stars="4"></div>
                                </div>
                            </div>

                            <form class="grid gap-3 px-4 py-3 border-t border-white/10" @submit.prevent="submitReview">
                                <div class="flex items-center gap-2 text-white">
                                    <label for="ratingSelect" class="text-sm">Your rating</label>
                                    <select id="ratingSelect" v-model.number="ratingSelect"
                                        class="bg-[#0b1024] border border-white/10 rounded-lg px-3 py-2 text-white">
                                        <option :value="5">★★★★★</option>
                                        <option :value="4">★★★★☆</option>
                                        <option :value="3">★★★☆☆</option>
                                        <option :value="2">★★☆☆☆</option>
                                        <option :value="1">★☆☆☆☆</option>
                                    </select>
                                </div>
                                <textarea id="reviewText" rows="3" v-model="reviewText"
                                    placeholder="Share your thoughts about this course..."
                                    class="bg-[#0b1024] border border-white/10 rounded-xl px-3 py-2 text-white placeholder:text-[#9AA6D7]"></textarea>
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        class="px-4 py-2 rounded-lg font-semibold border border-white/10 text-white"
                                        style="background:linear-gradient(180deg,#1b2240,#141b33);">Cancel</button>
                                    <button type="submit"
                                        class="px-4 py-2 rounded-lg font-semibold text-white border border-white/20"
                                        style="background:linear-gradient(180deg,#5B8CFF,#7B61FF);">Submit
                                        review</button>
                                </div>
                            </form>
                        </section>
                    </div>

                    <!-- RIGHT: Purchase + Instructor -->
                    <aside class="space-y-4">
                        <div class="rounded-2xl overflow-hidden border border-white/10 shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)]"
                            style="background:linear-gradient(180deg,#1b2240,#141b33);">
                            <div class="h-40 bg-white bg-center bg-cover"></div>
                            <div class="p-4 gap-2">
                                <div class="text-white text-2xl font-extrabold">
                                    {{ course.is_paid ? course.price + ' MMK' : 'free' }}
                                </div>
                                <div class="text-[#9AA6D7] text-sm">Full lifetime access | Quiz | Certificate</div>
                                <div class="h-px bg-white/10 my-1"></div>

                                <div class="gap-2 text-[#B8C4ED] text-sm">
                                    <div class="flex items-center gap-2">
                                        <svg width="16" height="16" fill="#7CF8C4" viewBox="0 0 24 24">
                                            <path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                                        </svg>
                                        Lifetime all lessons access
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg width="16" height="16" fill="#7CF8C4" viewBox="0 0 24 24">
                                            <path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                                        </svg>
                                        Downloadable resources
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg width="16" height="16" fill="#7CF8C4" viewBox="0 0 24 24">
                                            <path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                                        </svg>
                                        Mentor Q&A forum access
                                    </div>
                                </div>

                                <div class="h-px bg-white/10 my-1"></div>
                                <div
                                    class="w-full text-center cursor-pointer px-4 py-3 rounded-lg font-semibold text-white border border-white/20 bg-sky-700"
                                    v-if="courseStatus && courseStatus != 'rejected'"
                                    >
                                    {{ courseStatus == 'pending' ? 'Reviewing your payment...' : 'Paid' }}
                                </div>

                                <button
                                    v-else
                                    class="w-full px-4 py-3 rounded-lg font-semibold text-white border border-white/20 bg-sky-800"
                                    @click="openBuyModal">
                                    Buy course
                                </button>

                                <div class="flex items-center gap-2 text-[#9AA6D7] text-xs mt-3">
                                    <svg width="16" height="16" fill="#AFC7FF" viewBox="0 0 24 24">
                                        <path d="M12 1 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z" />
                                    </svg>
                                    We will review and respond within 24 hours to your payment
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl overflow-hidden border border-white/10 shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)]"
                            style="background:linear-gradient(180deg,#1b2240,#141b33);">
                            <div class="px-4 py-3 border-b border-white/10">
                                <div class="font-extrabold text-white">Instructor</div>
                            </div>
                            <div class="flex items-center gap-3 p-4">
                                <div
                                    class="w-12 h-12 rounded-xl border border-white/10 bg-[url('https://images.unsplash.com/photo-1547425260-76bcadfb4f2c?q=80&w=300&auto=format&fit=crop')] bg-center bg-cover">
                                </div>
                                <div>
                                    <div class="text-white font-semibold">Alex Rivera</div>
                                    <div class="text-[#9AA6D7] text-sm">Senior Frontend Engineer • 120k students</div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </main>

        <!-- Toast -->
        <div ref="toastEl"
            class="fixed left-1/2 -translate-x-1/2 bottom-24 opacity-0 pointer-events-none px-3 py-2 rounded-xl text-white border border-white/20 transition shadow-[0_10px_30px_rgba(0,0,0,0.35)] bg-[linear-gradient(180deg,#1b2240,#141b33)]">
            This lesson is locked. Buy the course to continue watching.
        </div>

        <!-- BUY MODAL -->
        <div v-show="buyModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            @click.self="closeBuyModal">
            <div
                class="w-full max-w-lg rounded-2xl overflow-hidden border border-white/10 shadow-[0_10px_30px_rgba(0,0,0,0.35)] bg-[linear-gradient(180deg,#1b2240,#141b33)]">
                <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
                    <div class="font-extrabold text-white">Complete your purchase</div>
                    <button class="px-3 py-1 rounded-full border border-white/10 text-white"
                        @click="closeBuyModal">Close</button>
                </div>

                <div class="p-4 gap-4">
                    <!-- QR code -->
                    <div class="w-full place-items-center">
                        <img src="/mmqr.jpg" alt="Payment QR"
                            class="w-48 h-48 rounded-lg border border-white/10 object-contain bg-[#0b1024]" />

                    </div>

                    <div class="my-2">
                        <p class="flex justify-between my-1">
                            <span class="text-lg">Course Code</span>
                            <span>{{ course.course_code }}</span>
                        </p>
                        <p class="flex justify-between my-1">
                            <span class="text-lg">Course Title</span>
                            <span>{{ course.title }}</span>
                        </p>
                        <p class="flex justify-between my-1">
                            <span class="text-lg">Total Lessons</span>
                            <span>{{ course.lessons.length }}</span>
                        </p>
                        <p class="flex justify-between my-1">
                            <span class="text-lg">Course Price</span>
                            <span>{{ course.price }} MMK</span>
                        </p>
                    </div>
                    <hr class="my-2 border-sky-200">
                    <!-- Upload form -->
                    <form @submit.prevent="submitPayment" class="gap-3">
                        <input type="hidden" name="course_id" :value="course.id" />
                        <input type="hidden" name="fee" :value="course.price" />
                        <div>
                            <label class="block text-sm text-white mb-1">Upload payment screenshot</label>
                            <input type="file" accept="image/*" @change="onFileChange" class="block w-full text-white file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border file:border-white/10 file:bg-[#1b2240] file:text-white file:cursor-pointer
                            border border-white/10 rounded-lg bg-[#0b1024] p-2" />
                            <p v-if="form.errors.proof" class="text-pink-300 text-xs mt-1">{{ form.errors.proof }}</p>
                        </div>
                        <div>
                            <label class="block text-sm text-white mb-1">Note (optional)</label>
                            <textarea v-model="form.note" rows="3" placeholder="Any details about your payment..."
                                class="w-full bg-[#0b1024] border border-white/10 rounded-lg px-3 py-2 text-white placeholder:text-[#9AA6D7]"></textarea>
                        </div>
                        <small class="text-sky-500">
                            After submitting your payment and uploading the proof (such as a screenshot or receipt), our team will review and confirm your payment within 24 hours.
                        </small>
                        <small class=" text-sky-700 text-center block">
                            Contact Mail - staff@skillforge.com
                        </small>
                        <small class=" text-sky-700 text-center block">
                            Contact Phone - 09978114491
                        </small>
                        <button type="submit" :disabled="form.processing"
                            class="w-full mt-3 px-4 bg-sky-800 py-3 rounded-lg font-semibold text-white border border-white/20 disabled:opacity-60"
                            >
                            {{ form.processing ? 'Submitting...' : 'Submit' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

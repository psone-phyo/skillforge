<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head, useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import customParseFormat from 'dayjs/plugin/customParseFormat';
import { ref, onMounted, watch } from 'vue';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios'
import { computed } from 'vue';
import { useI18n } from '@/composables/useI18n';

const { t, locale } = useI18n();
const page = usePage();
const user = page.props.auth.user;
const successMessage = ref('');
const toastMessage = ref('');
const toastVisible = ref(false);

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            showToast(flash.success);
        } else if (flash?.error) {
            showToast(flash.error);
        }
    },
    { deep: true, immediate: true }
);

dayjs.extend(relativeTime);
dayjs.extend(customParseFormat);

const breadcrumbs = [{ title: t('nav.dashboard'), href: dashboard().url }];

const props = defineProps<{
    course: any;      // { id, title, tags?, instructor, lessons: [{ id, title, video_url, is_locked?, duration?, duration_sec? }] }
    fileUrl: string;  // prefix for relative video URLs
    courseStatus: String;
    reviews: any[];
    avgRating: Number;
    quizScore: any;
    quizTotalScore: any
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
    toastMessage.value = message;
    toastVisible.value = true;
    setTimeout(() => {
        toastVisible.value = false;
    }, 3000);
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
    lessonTitle.value = locale == 'my' ? lesson.mm_title : lesson.title;
    currentLesson.value = index;
}

// Reviews
const ratingSelect = ref<number>(5);
const reviewText = ref<string>('');
const submiteForm = useForm({
    rating: ratingSelect,
    review: reviewText,
    course_id: props.course.id
});
const deleteForm = useForm({
    id: null as any
});
function submitReview() {
    const stars = ratingSelect.value;
    const text = reviewText.value.trim();
    if (stars && text) {
        submiteForm.rating = stars;
        submiteForm.review = text;
        submiteForm.post('/add-review', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                reviewText.value = '';
            },
        });
    }
}
function deleteReview(id: any) {
    deleteForm.id = id;
    deleteForm.get('/delete-review/' + id, { preserveScroll: true });
}

// Buy modal + upload proof
const buyModalOpen = ref(false);
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
function openBuyModal() { buyModalOpen.value = true; }
function closeBuyModal() { buyModalOpen.value = false; }
function submitPayment() {
    if (!form.course_id) form.course_id = props.course?.id ?? null;
    if (!form.fee || form.fee === 0) form.fee = props.course?.price ?? 0;
    form.post(`/course/${form.course_id}/payments`, {
        forceFormData: true,
        onSuccess: () => {
            closeBuyModal();
            showToast(t('messages.payment_received'));
        },
        onError: () => {
            showToast(t('messages.payment_error'));
        },
    });
}
const stripeProcessing = ref(false);
function submitPaymentWithStripe() {
    stripeProcessing.value = true;
    const course_id = props.course?.id;
    const fee = props.course?.price;
    axios.post('/stripe/create-payment', { course_id, fee })
        .then(response => { window.location.href = response.data.url; })
        .catch(error => {
            console.error('Payment error:', error);
            alert('Something went wrong while creating payment.');
        })
        .finally(() => { stripeProcessing.value = false; });
}

// file input handlers
function onFileChange(e: Event) {
    const files = (e.target as HTMLInputElement).files;
    form.proof = files && files[0] ? files[0] : null;
}

// QUIZ: modal, load, submit
const quizModalOpen = ref(false);
const quizLoading = ref(false);
const quizError = ref('');
const quiz = ref<any | null>(null); // { id, title, questions: [{ id, title, choices: [{ id, text }] }] }
const selectedAnswers = ref<Record<number, number>>({}); // question_id -> choice_id
const quizForm = useForm({
    course_id: null as number | null,
    quiz_id: null as number | null,
    answers: {} as Record<number, number>,
});

async function openQuizModal() {
    quizError.value = '';
    quizLoading.value = true;
    quiz.value = null;
    selectedAnswers.value = {};
    quizModalOpen.value = true;
    document.body.style.overflow = 'hidden';

    try {
        const { data } = await axios.get(`/course/${props.course.id}/quiz`);
        quiz.value = data;
        quizForm.course_id = props.course.id;
        quizForm.quiz_id = data.id;
    } catch (e: any) {
        quizError.value = e?.response?.data?.message || t('not_load_quiz');
    } finally {
        quizLoading.value = false;
    }
}

function closeQuizModal() {
    quizModalOpen.value = false;
    document.body.style.overflow = '';

}

const quizScore = ref<number>(
    Number((usePage().props as any)?.quizScore?.score ?? 0) // fallback from page props
);

// Passing score from course data
const passingScore = ref<number>(Number(props.course?.quiz?.passing_score ?? 0));


// Normalize both to [0,100]
const normalizedScore = computed(() => Math.max(0, Math.min(100, Number(quizScore.value ?? 0))));
const normalizedPassing = computed(() => Math.max(0, Math.min(100, passingScore.value)));

// SVG ring math
const R = 28; // radius
const C = 2 * Math.PI * R; // circumference

const scoreDash = computed(() => ({
    dasharray: `${C}`,
    dashoffset: `${C - (normalizedScore.value / props.quizTotalScore) * C}`,
}));

const passDash = computed(() => ({
    dasharray: `${C}`,
    dashoffset: `${C - (normalizedPassing.value / props.quizTotalScore) * C}`,
}));

// Optional: pass/fail status
const isPassed = computed(() => normalizedScore.value >= normalizedPassing.value);

async function submitQuiz() {
    if (!quiz.value) return;

    quizForm.answers = { ...selectedAnswers.value };

    const unanswered = (quiz.value.questions || []).filter((q: any) => !quizForm.answers[q.id]);
    if (unanswered.length > 0) {
        showToast(t('messages.answer_all'));
        return;
    }

    try {
        const response = await axios.post('/take-quiz', {
            quiz_id: quiz.value.id,
            answers: quizForm.answers,
        });

        const data = response.data;

        quizScore.value = data.score ?? 0;
        passingScore.value = data.passed ?? 0;
        showToast(t('messages.quiz_submitted')+`: ${quizScore.value}`);

        closeQuizModal();

    } catch (error) {
        showToast(t('messages.error_submit'));
        console.error(error);
    }
}

onMounted(async () => {
    const first = props.course?.lessons?.[0];
    isLocked.value = !!first?.is_locked;
    currentVideoSrc.value = buildSrc(first?.video_url || '');
    lessonTitle.value = locale == 'my' ? first?.mm_title : first?.title || '';
    currentLesson.value = 0;

    document.querySelectorAll<HTMLElement>('.r-stars[data-stars]').forEach((el) => {
        const v = Number(el.dataset.stars || '0');
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
            <div v-if="toastVisible"
                class="fixed top-5 right-5 px-4 py-3 rounded-lg text-white bg-green-600 shadow-lg z-50">
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
                                    <h3 class="text-[#9AA6D7] text-md md:text-lg font-semibold">{{ locale == 'my' ? course.mm_title : course.title ||
                                        'Course' }}</h3>
                                    <div class="flex flex-wrap items-center gap-2 text-[#9AA6D7] text-sm mt-1">
                                        <span>{{ t('course.rating') }} {{ avgRating.toFixed(1) }} ({{ reviews.length }} {{ t('course.reviews') }})</span>
                                        <span>•</span>
                                        <span>{{ course?.lessons?.length || 0 }} {{ t('course.lessons') }}</span>
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
                                <div class="font-extrabold text-white">{{ t('course.choose_curriculum') }}</div>
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
                                        <div class="text-white font-semibold">{{ locale == 'my' ? lesson.mm_title : lesson.title || `Lesson ${idx + 1}` }}
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
                                                    String(Math.floor((lesson.duration_sec || 0) % 60)).padStart(2, '0')
                                                }}
                                            </template>
                                            <template v-else>
                                                {{ lessonDurations[lesson.id ?? idx] || '' }}
                                            </template>
                                        </div>
                                    </div>

                                    <span v-if="!lesson.is_locked && courseStatus && courseStatus == 'approved'"
                                        class="px-2 py-1 rounded-md text-xs border border-green-300/40 text-green-200 bg-green-300/10">{{ t('course.unlocked') }}</span>
                                    <span v-else-if="!lesson.is_locked"
                                        class="px-2 py-1 rounded-md text-xs border border-emerald-300/40 text-emerald-200 bg-emerald-300/10">{{ t('course.free') }}</span>
                                    <span v-else
                                        class="px-2 py-1 rounded-md text-xs border border-pink-300/40 text-pink-200 bg-pink-300/10">{{ t('course.locked') }}</span>
                                </div>
                            </div>
                        </section>

                        <!-- Reviews (unchanged aside from layout) -->
                        <section
                            class="rounded-2xl overflow-hidden border border-white/10 shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)]"
                            style="background:linear-gradient(180deg,#1b2240,#141b33);">
                            <div class="flex items-center gap-4 px-4 py-3 border-b border-white/10">
                                <div>{{ t('course.reviews_ratings') }}</div>
                                <!-- <div class="text-4xl font-extrabold text-white">{{ avgRating.toFixed(1) }}</div> -->
                                <!-- <div ref="summaryStarsEl" class="inline-flex"></div> -->
                            </div>

                            <div ref="reviewsEl" class="px-4 py-2 divide-y divide-white/10">
                                <div class="text-center" v-show="reviews.length == 0">
                                    {{ t('course.no_reviews_ratings') }}
                                </div>
                                <div class=" grid-cols-[auto_1fr_auto] gap-3 py-3" v-for="review in reviews"
                                    :key="review.id">
                                    <div class="flex justify-between">
                                        <div class="flex items-center gap-2 mb-2">
                                            <img class="w-9 h-9 rounded-full border border-white/10 bg-center bg-cover"
                                                :src="review.user.profile_url" alt="Profile" />
                                            <div>
                                                <div class="text-white font-semibold">{{ review.user.name }}</div>
                                            </div>


                                        </div>
                                        <div>
                                            <a @click="deleteReview(review.id)"
                                                class="text-red-600 p-1 rounded-md cursor-pointer"
                                                v-if="review.user_id == user.id">
                                                <span class="material-icons">delete</span>
                                            </a>
                                        </div>
                                    </div>
                                    <!-- <div class="r-stars" :data-stars="review.rating"></div> -->
                                    <div class="text-yellow-300" v-show="review.rating == 5">★★★★★</div>
                                    <div class="text-yellow-300" v-show="review.rating == 4">★★★★☆</div>
                                    <div class="text-yellow-300" v-show="review.rating == 3">★★★☆☆</div>
                                    <div class="text-yellow-300" v-show="review.rating == 2">★★☆☆☆</div>
                                    <div class="text-yellow-300" v-show="review.rating == 1">★☆☆☆☆</div>

                                    <div class="text-[#C5CFEE] text-sm">{{ review.comment }}</div>

                                </div>


                            </div>

                            <form class="gap-3 px-4 py-3 border-t border-white/10" @submit.prevent="submitReview">
                                <div class="items-center gap-2 text-white">
                                    <label for="ratingSelect" class="text-sm">{{ t('course.rating') }}</label>
                                    <select id="ratingSelect" name="rating" v-model.number="ratingSelect"
                                        class="bg-[#0b1024] border border-white/10 rounded-lg px-3 py-2 text-white">
                                        <option :value="5">★★★★★</option>
                                        <option :value="4">★★★★☆</option>
                                        <option :value="3">★★★☆☆</option>
                                        <option :value="2">★★☆☆☆</option>
                                        <option :value="1">★☆☆☆☆</option>
                                    </select>
                                </div>
                                <textarea id="reviewText" rows="3" v-model="reviewText"
                                    :placeholder="t('course.review_placeholder')"
                                    class="bg-[#0b1024] border border-white/10 rounded-xl px-3 py-2 text-white placeholder:text-[#9AA6D7] w-full"></textarea>
                                <div class="flex justify-end gap-2">
                                    <button type="submit"
                                        class="px-4 py-2 rounded-lg font-semibold text-white border border-white/20 bg-sky-700">{{ t('course.submit_review') }}</button>
                                </div>
                            </form>
                        </section>
                    </div>

                    <!-- RIGHT: Purchase + Instructor -->
                    <aside class="space-y-4">
                        <div class="rounded-2xl overflow-hidden border border-white/10 shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)]"
                            style="background:linear-gradient(180deg,#1b2240,#141b33);">
                            <img class="h-40 bg-white bg-center w-full" :src="course.thumbnail_url"/>
                            <div class="p-4 gap-2">
                                <div class="text-white text-2xl font-extrabold">
                                    {{ course.is_paid ? course.price + ' ' + t('course.mmk') : t('course.free') }}
                                </div>
                                <div class="text-[#9AA6D7] text-sm">{{ t('course.payment_1') }}</div>
                                <div class="h-px bg-white/10 my-1"></div>

                                <div class="gap-2 text-[#B8C4ED] text-sm">
                                    <div class="flex items-center gap-2">
                                        <svg width="16" height="16" fill="#7CF8C4" viewBox="0 0 24 24">
                                            <path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                                        </svg>
                                       {{ t('course.payment_2') }}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg width="16" height="16" fill="#7CF8C4" viewBox="0 0 24 24">
                                            <path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                                        </svg>
                                        {{ t('course.payment_3') }}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg width="16" height="16" fill="#7CF8C4" viewBox="0 0 24 24">
                                            <path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                                        </svg>
                                        {{ t('course.payment_4') }}
                                    </div>
                                </div>

                                <div class="h-px bg-white/10 my-1"></div>
                                <div class="w-full text-center cursor-pointer px-4 py-3 rounded-lg font-semibold text-white border border-white/20 bg-sky-700"
                                    v-if="courseStatus && courseStatus == 'free'">
                                    {{ t('course.free') }} </div>

                                <div class="w-full text-center cursor-pointer px-4 py-3 rounded-lg font-semibold text-white border border-white/20 bg-sky-700"
                                    v-else-if="courseStatus && courseStatus != 'rejected' && courseStatus != 'free'">
                                    {{ courseStatus == 'pending' ? t('course.payment_review') : t('course.paid') }}
                                </div>

                                <button v-else
                                    class="cursor-pointer w-full px-4 py-3 rounded-lg font-semibold text-white border border-white/20 bg-sky-800"
                                    @click="openBuyModal">
                                    {{ t('course.buy_course') }}
                                </button>

                                <!-- Take Quiz button -->
                                <button v-show="courseStatus && (courseStatus == 'approved' || courseStatus == 'free')"
                                    class="cursor-pointer w-full mt-2 px-4 py-3 rounded-lg font-semibold text-white border border-white/20 bg-indigo-700 hover:bg-indigo-800"
                                    @click="openQuizModal" v-if="!isPassed">
                                    {{ t('course.take_quiz') }}
                                </button>
                                <a v-if="courseStatus && (courseStatus == 'approved' || courseStatus == 'free')"
                                    v-show="isPassed"
                                    class="block text-center cursor-pointer w-full mt-2 px-4 py-3 rounded-lg font-semibold text-white border border-white/20 bg-indigo-700 hover:bg-indigo-800"
                                    :href="`/get/certificate/${course.id}`">
                                    {{ t('course.get_certificate') }}
                                </a>

                                <div class="flex items-center gap-2 text-[#9AA6D7] text-xs mt-3">
                                    <svg width="16" height="16" fill="#AFC7FF" viewBox="0 0 24 24">
                                        <path d="M12 1 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z" />
                                    </svg>
                                    {{ t('course.terms') }}
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl overflow-hidden border border-white/10 shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)]"
                            style="background:linear-gradient(180deg,#1b2240,#141b33);">
                            <div class="px-4 py-3 border-b border-white/10">
                                <div class="font-extrabold text-white">{{ t('course.instructor') }}</div>
                            </div>
                            <div class="flex items-center gap-3 p-4">
                                <img class="w-12 h-12 rounded-xl border border-white/10 bg-center bg-cover"
                                    :src="course.instructor.profile_url" />
                                <div>
                                    <div class="text-white font-semibold">{{ course.instructor.name }}</div>
                                    <div class="text-[#9AA6D7] text-sm">{{ course.instructor.title }} • {{
                                        course.instructor.courses.length
                                        }} {{ t('course.courses') }}</div>
                                </div>
                            </div>
                            <a class="rounded-md text-center bg-sky-800 p-3 block cursor-pointer"
                            :href="`/make/chat/${course.instructor.id}`"
                            >
                                {{ t('course.chat_instructor') }}
                            </a>
                        </div>

                        <!-- Quiz Progress -->
                        <div class="rounded-2xl overflow-hidden border border-white/10 shadow-[0_10px_30px_rgba(0,0,0,0.35),_0_2px_6px_rgba(0,0,0,0.2)]"
                            style="background:linear-gradient(180deg,#1b2240,#141b33);">
                            <div class="px-4 py-3 border-b border-white/10 flex items-center justify-between">
                                <div class="font-extrabold text-white">{{ t('course.quiz_progress') }}</div>
                                <span :class="[
                                    'text-xs px-2 py-1 rounded-md border',
                                    isPassed ? 'text-emerald-200 border-emerald-300/40 bg-emerald-300/10' : 'text-pink-200 border-pink-300/40 bg-pink-300/10'
                                ]">
                                    {{ isPassed ? t('course.passed') : t('course.not_passed') }}
                                </span>
                            </div>

                            <div class="p-4 grid grid-cols-2 gap-4">
                                <!-- Score -->
                                <div class="flex items-center gap-3">
                                    <svg width="72" height="72" viewBox="0 0 72 72" class="shrink-0">
                                        <!-- track -->
                                        <circle cx="36" cy="36" :r="R" stroke="#273159" stroke-width="8" fill="none" />
                                        <!-- progress -->
                                        <circle cx="36" cy="36" :r="R" stroke="url(#scoreGrad)" stroke-width="8"
                                            fill="none" :stroke-dasharray="scoreDash.dasharray"
                                            :stroke-dashoffset="scoreDash.dashoffset" stroke-linecap="round"
                                            transform="rotate(-90 36 36)" />
                                        <defs>
                                            <linearGradient id="scoreGrad" x1="0" y1="0" x2="72" y2="72"
                                                gradientUnits="userSpaceOnUse">
                                                <stop stop-color="#5B8CFF" />
                                                <stop offset="1" stop-color="#7B61FF" />
                                            </linearGradient>
                                        </defs>
                                    </svg>
                                    <div>
                                        <div class="text-white font-semibold">{{ t('course.score') }}</div>
                                        <div class="text-[#C5CFEE] text-sm">{{ normalizedScore }}</div>
                                    </div>
                                </div>

                                <!-- Passing -->
                                <div class="flex items-center gap-3">
                                    <svg width="72" height="72" viewBox="0 0 72 72" class="shrink-0">
                                        <!-- track -->
                                        <circle cx="36" cy="36" :r="R" stroke="#273159" stroke-width="8" fill="none" />
                                        <!-- progress -->
                                        <circle cx="36" cy="36" :r="R" stroke="url(#passGrad)" stroke-width="8"
                                            fill="none" :stroke-dasharray="passDash.dasharray"
                                            :stroke-dashoffset="passDash.dashoffset" stroke-linecap="round"
                                            transform="rotate(-90 36 36)" />
                                        <defs>
                                            <linearGradient id="passGrad" x1="0" y1="0" x2="72" y2="72"
                                                gradientUnits="userSpaceOnUse">
                                                <stop stop-color="#42E3B4" />
                                                <stop offset="1" stop-color="#29C2A0" />
                                            </linearGradient>
                                        </defs>
                                    </svg>
                                    <div>
                                        <div class="text-white font-semibold">{{ t('course.passing_score') }}</div>
                                        <div class="text-[#C5CFEE] text-sm">{{ normalizedPassing }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Raw values display -->
                            <div class="px-4 pb-4 text-[#9AA6D7] text-xs">
                                {{ t('course.your_score') }}: {{ (quizScore ?? 0) }} / {{ (quizTotalScore ?? 0) }}
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </main>

        <!-- Toast -->
        <!-- <div ref="toastEl"
            class="fixed left-1/2 -translate-x-1/2 bottom-24 opacity-0 pointer-events-none px-3 py-2 rounded-xl text-white border border-white/20 transition shadow-[0_10px_30px_rgba(0,0,0,0.35)] bg-[linear-gradient(180deg,#1b2240,#141b33)]">
            This lesson is locked. Buy the course to continue watching.
        </div> -->

        <!-- BUY MODAL -->
        <div v-show="buyModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            @click.self="closeBuyModal">
            <div
                class="w-full max-w-lg rounded-2xl overflow-hidden border border-white/10 shadow-[0_10px_30px_rgba(0,0,0,0.35)] bg-[linear-gradient(180deg,#1b2240,#141b33)]">
                <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
                    <div class="font-extrabold text-white">{{ t('course.complete_purchase') }}</div>
                    <button class="px-3 py-1 rounded-full border border-white/10 text-white"
                        @click="closeBuyModal">{{ t('course.close') }}</button>
                </div>

                <div class="p-4 gap-4">
                    <!-- QR code -->
                    <div class="w-full place-items-center">
                        <img src="/mmqr.jpg" alt="Payment QR"
                            class="w-48 h-48 rounded-lg border border-white/10 object-contain bg-[#0b1024]" />

                    </div>

                    <div class="my-2">
                        <p class="flex justify-between my-1">
                            <span class="text-lg">{{ t('course.course_code') }}</span>
                            <span>{{ course.course_code }}</span>
                        </p>
                        <p class="flex justify-between my-1">
                            <span class="text-lg">{{ t('course.course_title') }}</span>
                            <span>{{ course.title }}</span>
                        </p>
                        <p class="flex justify-between my-1">
                            <span class="text-lg">{{ t('course.total_lessons') }}</span>
                            <span>{{ course.lessons.length }}</span>
                        </p>
                        <p class="flex justify-between my-1">
                            <span class="text-lg">{{ t('course.course_price') }}<</span>
                            <span>{{ course.price }} {{ t('course.mmk') }}</span>
                        </p>
                    </div>
                    <hr class="my-2 border-sky-200">
                    <!-- Upload form -->
                    <form @submit.prevent="submitPayment" class="gap-3">
                        <input type="hidden" name="course_id" :value="course.id" />
                        <input type="hidden" name="fee" :value="course.price" />
                        <div>
                            <label class="block text-sm text-white mb-1">{{ t('course.upload_ss') }}</label>
                            <input type="file" accept="image/*" @change="onFileChange" class="block w-full text-white file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border file:border-white/10 file:bg-[#1b2240] file:text-white file:cursor-pointer
                            border border-white/10 rounded-lg bg-[#0b1024] p-2" />
                            <p v-if="form.errors.proof" class="text-pink-300 text-xs mt-1">{{ form.errors.proof }}</p>
                        </div>
                        <div>
                            <label class="block text-sm text-white mb-1">{{ t('course.note_optional') }}</label>
                            <textarea v-model="form.note" rows="3" :placeholder="t('course.purchase_placeholder')"
                                class="w-full bg-[#0b1024] border border-white/10 rounded-lg px-3 py-2 text-white placeholder:text-[#9AA6D7]"></textarea>
                        </div>
                        <small class="text-sky-500">
                            {{ t('course.purchase_tip') }}
                        </small>
                        <small class=" text-sky-700 text-center block">
                            {{ t('course.email') }}
                        </small>
                        <small class=" text-sky-700 text-center block">
                           {{ t('course.phone') }}
                        </small>
                        <button type="submit" :disabled="form.processing || stripeProcessing"
                            class="w-full mt-3 px-4 bg-sky-800 py-3 rounded-lg font-semibold text-white border border-white/20 disabled:opacity-60">
                            {{ form.processing ? t('course.submitting') : t('course.submit')  }}
                        </button>
                        <h3 class="text-center">{{ t('course.or') }}</h3>
                    </form>
                    <form @submit.prevent="submitPaymentWithStripe">
                        <input type="hidden" name="course_id" :value="course.id" />
                        <input type="hidden" name="fee" :value="course.price" />
                        <button type="submit" :disabled="stripeProcessing || form.processing"
                            class="w-full mt-3 px-4 bg-sky-800 py-3 rounded-lg font-semibold text-white border border-white/20 disabled:opacity-60">
                            {{ stripeProcessing ? t('course.redirecting') : t('course.pay_card') }}

                        </button>
                    </form>
                    <!-- QUIZ MODAL -->
                </div>
            </div>
        </div>

        <!-- QUIZ MODAL -->
        <div v-show="quizModalOpen"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            @click.self="closeQuizModal">
            <div
                class="w-full max-w-2xl max-h-[90vh] overflow-hidden rounded-2xl border border-white/10 shadow-[0_10px_30px_rgba(0,0,0,0.35)] bg-[linear-gradient(180deg,#1b2240,#141b33)]">
                <!-- Sticky Header -->
                <div
                    class="sticky top-0 z-10 flex items-center justify-between px-4 py-3 border-b border-white/10 bg-[linear-gradient(180deg,#1b2240,#141b33)]">
                    <div class="font-extrabold text-white">
                        {{ quizLoading ? 'Loading quiz...' : (locale == 'my' ? quiz?.mm_title : quiz?.title || 'Course Quiz') }}
                    </div>
                    <button class="px-3 py-1 rounded-full border border-white/10 text-white"
                        @click="closeQuizModal">{{ t('course.close') }}</button>
                </div>

                <!-- Scrollable content area -->
                <div class="p-4 grid gap-4 overflow-y-auto max-h-[calc(90vh-56px)]">
                    <div v-if="quizError" class="text-pink-300 text-sm">{{ quizError }}</div>
                    <div v-else-if="quizLoading" class="text-[#9AA6D7]">Please wait...</div>

                    <form v-else-if="quiz" @submit.prevent="submitQuiz" class="grid gap-5">
                        <input type="hidden" name="course_id" :value="course.id" />
                        <input type="hidden" name="quiz_id" :value="quiz.id" />

                        <div v-for="(q, qi) in quiz.quiz_questions" :key="q.id" class="grid gap-2">
                            <div class="text-white font-semibold">
                                Q{{ qi + 1 }}. {{ q.question }}
                            </div>
                            <div class="grid gap-2">
                                <label v-for="choice in q.options" :key="choice.id"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg border border-white/10 text-white bg-[#101733] hover:bg-[#0f1733] cursor-pointer">
                                    <input class="accent-blue-500" type="radio" :name="'q-' + q.id" :value="choice.id"
                                        v-model="selectedAnswers[q.id]" />
                                    <span>{{ choice.answer }}</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="sticky bottom-0 flex justify-end gap-2 pt-2 bg-[linear-gradient(180deg,rgba(27,34,64,0.95),rgba(20,27,51,0.95))]">
                            <button type="button"
                                class="px-4 py-2 rounded-lg font-semibold border border-white/10 text-white"
                                style="background:linear-gradient(180deg,#1b2240,#141b33);" @click="closeQuizModal">
                                {{ t('course.cancel') }}
                            </button>
                            <button type="submit"
                                class="px-4 py-2 rounded-lg font-semibold text-white border border-white/20 bg-sky-500">
                                {{ t('course.submit_quiz') }}
                            </button>
                        </div>
                    </form>

                    <div v-else class="text-[#9AA6D7]">{{ t('course.no_quiz') }}</div>
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

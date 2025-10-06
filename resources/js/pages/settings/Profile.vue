<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import { getInitials } from '@/composables/useInitials';

const props = defineProps<{
    mustVerifyEmail: boolean;
    status?: string;
    user: any; // object with name, email, profile_url
    fileUrl: string;
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Profile settings', href: edit().url },
];

// Local ref for preview
const profilePreview = ref(props.user.profile_url || '');
const profileFile = ref<File | null>(null);

// Computed initials if no profile image
const profileInitials = computed(() => getInitials(props.user.name || ''));

// Handle file selection
function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (!target.files || !target.files[0]) return;

    profileFile.value = target.files[0];

    // Show preview immediately
    const reader = new FileReader();
    reader.onload = (e) => {
        profilePreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(profileFile.value);
}

// Trigger hidden file input when clicking image
function triggerFileInput() {
    const input = document.getElementById('profile_input') as HTMLInputElement;
    input?.click();
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your name and email address" />

                <Form v-bind="ProfileController.update.form()" class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }">

                    <div class="grid gap-2">
                        <Label for="profile_url">Profile Image</Label>

                        <!-- Clickable image -->
                        <div class="mt-3 cursor-pointer w-32 h-32 rounded-full border border-neutral-700 overflow-hidden flex items-center justify-center text-xl font-bold text-white bg-neutral-800"
                            @click="triggerFileInput">

                            <!-- Show profile image if exists -->
                            <img v-if="profilePreview" :src="profilePreview" alt="Profile image"
                                class="w-full h-full object-cover" />

                            <!-- Show initials if no image -->
                            <span v-else>{{ profileInitials }}</span>
                        </div>

                        <!-- Hidden file input -->
                        <input id="profile_input" type="file" name="profile_url" accept="image/*" class="hidden"
                            @change="handleFileChange" />
                    </div>



                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" class="mt-1 block w-full" name="name" :default-value="user.name" required
                            autocomplete="name" placeholder="Full name" />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input id="email" type="email" class="mt-1 block w-full" name="email"
                            :default-value="user.email" required autocomplete="username" placeholder="Email address" />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link :href="send()" as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500">
                            Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email
                            address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" data-test="update-profile-button">Save</Button>

                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-show="recentlySuccessful" class="text-sm text-neutral-600">
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>

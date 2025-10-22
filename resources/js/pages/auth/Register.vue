<script setup lang="ts">
import RegisteredUserController from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
  categories: any[],
}>();

const selectedCategory = ref('');
const role = ref<'student' | 'instructor'>('student');
const cvFile = ref<File | null>(null);

function onCvChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0] || null;
  cvFile.value = file || null;
}
</script>

<template>
  <AuthBase title="Create an account" description="Enter your details below to create your account">
    <Head title="Register" />

    <Form
      v-bind="RegisteredUserController.store.form()"
      :reset-on-success="['password', 'password_confirmation']"
      v-slot="{ errors, processing }"
      class="flex flex-col gap-6"
    >
      <div class="grid gap-6">
        <!-- Role Tabs -->
        <div>
          <Label class="mb-2 block">Register as</Label>
          <div class="inline-flex rounded-lg border border-white/10 overflow-hidden">
            <button
              type="button"
              class="px-4 py-2 text-sm font-semibold"
              :class="role === 'student' ? 'bg-[linear-gradient(180deg,#1b2240,#141b33)] text-white' : 'bg-[#0b1024] text-[#C3CCF3]'"
              @click="role = 'student'"
            >
              Student
            </button>
            <button
              type="button"
              class="px-4 py-2 text-sm font-semibold border-l border-white/10"
              :class="role === 'instructor' ? 'bg-[linear-gradient(180deg,#1b2240,#141b33)] text-white' : 'bg-[#0b1024] text-[#C3CCF3]'"
              @click="role = 'instructor'"
            >
              Instructor
            </button>
          </div>
          <!-- Hidden input to send role -->
          <input type="hidden" name="role" :value="role" />
          <InputError :message="errors.role" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="grid gap-2">
          <Label for="name">Name</Label>
          <input
            id="name"
            type="text"
            required
            autofocus
            :tabindex="1"
            autocomplete="name"
            name="name"
            placeholder="Full name"
            class="w-full appearance-none rounded-lg border border-white/10 bg-[#0b1024] px-3 py-2 text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2e3c77]"
          />
          <InputError :message="errors.name" />
        </div>

        <!-- Email -->
        <div class="grid gap-2">
          <Label for="email">Email address</Label>
          <input
            id="email"
            type="email"
            required
            :tabindex="2"
            autocomplete="email"
            name="email"
            placeholder="email@example.com"
            class="w-full appearance-none rounded-lg border border-white/10 bg-[#0b1024] px-3 py-2 text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2e3c77]"
          />
          <InputError :message="errors.email" />
        </div>

        <!-- Category -->
        <div class="grid gap-2">
          <Label for="category">Category</Label>
          <div class="relative">
            <select
              id="category"
              name="interest_id"
              v-model="selectedCategory"
              required
              class="w-full appearance-none rounded-lg border border-white/10 bg-[#0b1024] px-3 py-2 text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2e3c77]"
            >
              <option value="" disabled>Select a category</option>
              <option
                v-for="category in props.categories"
                :key="category.id"
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
            <svg
              class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
            </svg>
          </div>
          <InputError :message="errors.interest_id || errors.category_id" />
        </div>

        <!-- Instructor-only fields -->
        <div v-if="role === 'instructor'" class="grid gap-2">
          <Label for="proposal">Proposal</Label>
          <textarea
            id="proposal"
            name="proposal"
            required
            rows="4"
            class="w-full rounded-lg border border-white/10 bg-[#0b1024] px-3 py-2 text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2e3c77]"
            placeholder="Tell us briefly about your expertise and what you plan to teach."
          ></textarea>
          <InputError :message="errors.proposal" />
        </div>

        <div v-if="role === 'instructor'" class="grid gap-2">
          <Label for="cv">Upload CV (PDF/DOC/DOCX)</Label>
          <input
            id="cv"
            name="cv"
            type="file"
            required
            accept=".pdf,.jpg,.png"
            class="block w-full cursor-pointer rounded-lg border border-white/10 bg-[#0b1024] px-3 py-2 text-white file:mr-4 file:rounded-md file:border-0 file:bg-[#1b2240] file:px-3 file:py-1 file:text-white focus:outline-none focus:ring-2 focus:ring-[#2e3c77]"
            @change="onCvChange"
          />
          <InputError :message="errors.cv" />
        </div>

        <!-- Password -->
        <div class="grid gap-2">
          <Label for="password">Password</Label>
          <input
            id="password"
            type="password"
            required
            :tabindex="3"
            autocomplete="new-password"
            name="password"
            placeholder="Password"
            class="w-full appearance-none rounded-lg border border-white/10 bg-[#0b1024] px-3 py-2 text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2e3c77]"
          />
          <InputError :message="errors.password" />
        </div>

        <!-- Confirm password -->
        <div class="grid gap-2">
          <Label for="password_confirmation">Confirm password</Label>
          <input
            id="password_confirmation"
            type="password"
            required
            :tabindex="4"
            autocomplete="new-password"
            name="password_confirmation"
            placeholder="Confirm password"
            class="w-full appearance-none rounded-lg border border-white/10 bg-[#0b1024] px-3 py-2 text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2e3c77]"
          />
          <InputError :message="errors.password_confirmation" />
        </div>

        <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="processing" data-test="register-user-button">
          <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
          Create account
        </Button>
      </div>

      <div class="text-center text-sm text-muted-foreground">
        Already have an account?
        <TextLink :href="login()" class="underline underline-offset-4" :tabindex="6">Log in</TextLink>
      </div>
    </Form>
  </AuthBase>
</template>

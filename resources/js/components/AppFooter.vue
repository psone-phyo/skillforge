<script setup lang="ts">
import { computed } from 'vue';

const year = computed(() => new Date().getFullYear());
</script>

<template>
  <footer
    class="border-t border-white/10 text-[#C3CCF3] bg-[linear-gradient(180deg,#141b33,#101733)]"
  >
    <div class="mx-auto max-w-7xl px-4 py-6">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <!-- Left: Brand and tagline -->
        <div class="flex items-center gap-3">
            <img src="/app_logo.png" class="size-5">
          <div class="font-semibold text-white">SkillForge LMS</div>
          <span class="hidden md:inline text-sm text-[#9AA6D7]">Learn, Discuss, Grow</span>
        </div>

        <!-- Middle: Links -->
        <nav class="flex flex-wrap items-center gap-4 text-sm">
          <a href="/about" class="hover:text-white">About</a>
          <a href="/courses" class="hover:text-white">Courses</a>
          <a href="/my-library" class="hover:text-white">My Library</a>
          <a href="/support" class="hover:text-white">Support</a>
          <a href="/privacy" class="hover:text-white">Privacy</a>
          <a href="/terms" class="hover:text-white">Terms</a>
        </nav>

        <!-- Right: Copyright -->
        <div class="text-sm text-[#9AA6D7]">
          © {{ year }} SkillForge. All rights reserved.
        </div>
      </div>
    </div>
  </footer>
</template>

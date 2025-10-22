<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import AppFooter from '@/components/AppFooter.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
  breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
});
</script>

<template>
  <AppShell class="flex-col min-h-screen">
    <AppHeader :breadcrumbs="breadcrumbs" />
    <AppContent>
      <slot />
    </AppContent>
    <AppFooter />
  </AppShell>
</template>

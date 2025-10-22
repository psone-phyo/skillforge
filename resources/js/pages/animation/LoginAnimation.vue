<template>
  <div ref="lottieContainer"></div>
</template>

<script setup>
import { onMounted, ref, onBeforeUnmount } from 'vue'
import lottie from 'lottie-web'
import animationData from '@/pages/animation/assets/Quiz_mode.json' // adjust path

const lottieContainer = ref(null)
let animation = null

onMounted(() => {
  animation = lottie.loadAnimation({
    container: lottieContainer.value,
    renderer: 'svg',
    loop: true,
    autoplay: true,
    animationData: animationData
  })
})

onBeforeUnmount(() => {
  if (animation) animation.destroy()
})
</script>

<style scoped>
/* Optional: set size */
</style>

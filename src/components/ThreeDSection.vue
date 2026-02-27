<script setup>
import { inject, ref } from 'vue'

const placeholder = true
const isDark = inject('isDark', ref(true))
</script>

<template>
  <div class="h-[500px] w-full relative overflow-hidden rounded-3xl border" 
       :class="isDark ? 'border-white/5 bg-black/60' : 'border-m-obsidian/10 bg-white/40'">
      <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-12">
        <h2 class="text-xs tracking-[1em] text-m-gold uppercase font-bold mb-4" 
            :class="isDark ? 'opacity-70' : 'opacity-70'">L'Excellence Interactive</h2>
        <h3 class="text-5xl md:text-7xl font-display font-bold uppercase leading-tight" 
            :class="isDark ? 'text-white' : 'text-m-obsidian'">La Géométrie <br/> du Goût</h3>
        <p class="mt-6 max-w-sm text-sm" 
           :class="isDark ? 'text-white/70' : 'text-m-obsidian/60'">Une expérience visuelle exceptionnelle (bientôt disponible).</p>
      </div>
  </div>
</template>

<style scoped>
canvas { touch-action: none; }
</style>

<script setup>
import { Star, StarHalf } from 'lucide-vue-next'
import { inject, ref } from 'vue'

const props = defineProps({
  rating: {
    type: Number,
    required: true,
    validator: (v) => v >= 0 && v <= 6
  },
  maxStars: {
    type: Number,
    default: 6
  },
  editable: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:rating'])
const isDark = inject('isDark', ref(true))

const setRating = (r) => {
  if (props.editable) {
    emit('update:rating', r)
  }
}
</script>

<template>
  <div class="flex items-center gap-0.5">
    <button 
      v-for="i in maxStars" 
      :key="i"
      @click="setRating(i)"
      :disabled="!editable"
      class="transition-transform hover:scale-110 disabled:hover:scale-100"
    >
      <Star 
        class="w-4 h-4"
        :class="[
          i <= Math.floor(rating) 
            ? 'star-active' 
            : isDark 
              ? 'text-gray-600 fill-transparent opacity-30'
              : 'text-gray-400 fill-transparent opacity-30'
        ]"
      />
    </button>
    <span class="ml-2 text-xs font-bold opacity-70">{{ rating }}/6</span>
  </div>
</template>

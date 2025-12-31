<template>
  <section id="faq" class="section">
    <div class="container mx-auto px-4">
      <h2 class="section-title">{{ $t('faq.title') }}</h2>
      <div class="max-w-3xl mx-auto space-y-4">
        <div v-for="(faq, index) in faqs" :key="index" class="content-card">
          <button
            @click="toggleFAQ(index)"
            class="w-full flex justify-between items-center text-left group"
          >
            <span class="font-semibold text-lg pr-4 group-hover:text-purple-600 transition-colors">
              {{ $t(faq.questionKey) }}
            </span>
            <span class="text-2xl text-purple-600 transition-transform" :class="{ 'rotate-45': faq.isOpen }">
              +
            </span>
          </button>
          <Transition name="expand">
            <div v-if="faq.isOpen" class="mt-4 text-gray-600 leading-relaxed">
              <p>{{ $t(faq.answerKey) }}</p>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface FAQItem {
  questionKey: string
  answerKey: string
  isOpen: boolean
}

const faqs = ref<FAQItem[]>([
  { questionKey: 'faq.q1.question', answerKey: 'faq.q1.answer', isOpen: false },
  { questionKey: 'faq.q2.question', answerKey: 'faq.q2.answer', isOpen: false },
  { questionKey: 'faq.q3.question', answerKey: 'faq.q3.answer', isOpen: false },
  { questionKey: 'faq.q4.question', answerKey: 'faq.q4.answer', isOpen: false },
  { questionKey: 'faq.q5.question', answerKey: 'faq.q5.answer', isOpen: false }
])

const toggleFAQ = (index: number) => {
  faqs.value[index].isOpen = !faqs.value[index].isOpen
}
</script>

<style scoped>
.expand-enter-active,
.expand-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  opacity: 0;
  max-height: 0;
}

.expand-enter-to,
.expand-leave-from {
  opacity: 1;
  max-height: 300px;
}
</style>

<template>
  <transition name="fade-comment">
    <div :class="['mb-2', `nested-level-${level}`]">
      <div
        :class="[
          'border rounded bg-white p-2 small shadow-sm',
          level > 0 ? 'ms-2 border-start border-3 border-primary' : 'mb-3'
        ]"
      >
        <div class="d-flex justify-content-between align-items-center mb-1">
          <strong>{{ comment.user_name }}</strong>
          <small class="text-muted">{{ comment.email }}</small>
        </div>

        <p v-html="sanitizeHtml(comment.text)" class="mb-2"></p>

        <div v-if="comment.attachment_path" class="mb-2">
          <div v-if="comment.attachment_type === 'image'" class="attachment-wrapper position-relative">
            <img
              :src="`/storage/${comment.attachment_path}`"
              class="rounded border w-100"
              style="max-width: 220px; cursor:pointer"
              @click="openLightbox(`/storage/${comment.attachment_path}`)"
            />
            <a
              :href="`/api/comments/${comment.id}/download`"
              class="download-button"
              title="Скачать"
              target="_blank"
            >⬇️</a>
          </div>
          <a
            v-else
            :href="`/api/comments/${comment.id}/download`"
            target="_blank"
            rel="noopener"
            class="text-decoration-none small"
          >📎 Скачать файл</a>
        </div>

        <div v-if="!showReply" class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-secondary px-2 py-1" @click="showReply = true">
            💬 Ответить
          </button>
        </div>

        <transition name="fade-reply">
          <div v-if="showReply">
            <comment-form
              :parent-id="comment.id"
              @comment-added="handleReply"
              class="mt-2"
            />
            <button class="btn btn-sm btn-outline-secondary mt-2" @click="showReply = false">
              ❌ Отменить
            </button>
          </div>
        </transition>

        <transition-group name="fade-child" tag="div" class="mt-2">
          <comment-item
            v-for="child in comment.children"
            :key="child.id"
            :comment="child"
            :level="level + 1"
            @comment-added="$emit('comment-added')"
          />
        </transition-group>
      </div>
    </div>
  </transition>

  <!-- LIGHTBOX -->
  <div v-if="showLightbox" class="lightbox-overlay" @click="closeLightbox">
    <img :src="lightboxImage" class="lightbox-img" @click.stop />
    <button class="lightbox-close" @click="closeLightbox">×</button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import CommentForm from './CommentForm.vue'
import CommentItem from './CommentItem.vue'

defineProps({ comment: Object, level: Number })
const emit = defineEmits(['comment-added'])

const showReply = ref(false)

// LIGHTBOX LOGIC
const lightboxImage = ref(null)
const showLightbox = ref(false)
function openLightbox(src) {
  lightboxImage.value = src
  showLightbox.value = true
}
function closeLightbox() {
  showLightbox.value = false
  lightboxImage.value = null
}

function sanitizeHtml(html) {
  const allowed = ['a', 'strong', 'i', 'code']
  const wrapper = document.createElement('div')
  wrapper.innerHTML = html
  wrapper.querySelectorAll('*').forEach(el => {
    if (!allowed.includes(el.tagName.toLowerCase())) {
      el.replaceWith(...el.childNodes)
    } else {
      [...el.attributes].forEach(attr => {
        if (!['href', 'title'].includes(attr.name)) el.removeAttribute(attr.name)
      })
    }
  })
  return wrapper.innerHTML
}

function handleReply() {
  showReply.value = false
  window.dispatchEvent(new Event('refresh-comments'))
}
</script>

<style scoped>
.nested-level-1 { margin-left: 12px; }
.nested-level-2 { margin-left: 20px; }
.nested-level-3 { margin-left: 28px; }

.attachment-wrapper {
  position: relative;
  display: inline-block;
  max-width: 220px;
}
.download-button {
  position: absolute;
  bottom: 6px;
  right: 6px;
  background-color: rgba(0, 0, 0, 0.6);
  color: white;
  font-size: 0.9rem;
  border-radius: 4px;
  padding: 4px 8px;
  opacity: 0;
  transition: opacity 0.3s ease;
}
.attachment-wrapper:hover .download-button {
  opacity: 1;
}

.fade-comment-enter-active,
.fade-comment-leave-active {
  transition: all 0.6s ease-in-out;
}
.fade-comment-enter-from,
.fade-comment-leave-to {
  opacity: 0;
  transform: translateY(16px);
}

.fade-reply-enter-active,
.fade-reply-leave-active {
  transition: all 0.5s ease-in-out;
}
.fade-reply-enter-from,
.fade-reply-leave-to {
  opacity: 0;
  transform: scale(0.97);
}

.fade-child-enter-active,
.fade-child-leave-active {
  transition: all 0.7s ease-in-out;
}
.fade-child-enter-from,
.fade-child-leave-to {
  opacity: 0;
  transform: translateY(12px);
}

/* LIGHTBOX STYLES */
.lightbox-overlay {
  position: fixed;
  inset: 0;
  z-index: 1050;
  background: rgba(0,0,0,0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.3s;
}
.lightbox-img {
  max-width: 96vw;
  max-height: 92vh;
  border-radius: 8px;
  box-shadow: 0 4px 32px #000a;
}
.lightbox-close {
  position: fixed;
  top: 2.5vh;
  right: 4vw;
  background: #fff;
  color: #000;
  border: none;
  border-radius: 100px;
  font-size: 2rem;
  line-height: 1;
  width: 48px;
  height: 48px;
  cursor: pointer;
  opacity: 0.9;
  z-index: 1060;
}
@keyframes fadeIn {
  from { opacity: 0 }
  to   { opacity: 1 }
}

/* Мобильная адаптация */
@media (max-width: 576px) {
  .d-flex.justify-content-between {
    flex-direction: column;
    align-items: flex-start !important;
    gap: 4px;
  }
  .comment-box { font-size: 0.9rem; }
  .btn, .btn-sm { width: 100%; margin-bottom: 0.5rem; }
  .attachment-wrapper img { max-width: 100%; height: auto; }
  .lightbox-close { top: 1.5vh; right: 2vw; width: 40px; height: 40px; font-size: 1.5rem; }
}
</style>

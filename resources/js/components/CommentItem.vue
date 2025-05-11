<template>
     <div :class="['mb-3', level > 0 ? `ms-${Math.min(level * 4, 12)}` : '']">
       <div :class="['card p-3', level > 0 ? 'bg-light border-start border-primary ps-4 small' : '']">
         <h6 class="mb-1">
           {{ comment.user_name }}
           <small class="text-muted">({{ comment.email }})</small>
         </h6>
   
         <p v-html="sanitizeHtml(comment.text)"></p>
   
         <!-- Вложение -->
         <div v-if="comment.attachment_path" class="mt-2">
           <img
             v-if="comment.attachment_type === 'image'"
             :src="`/storage/${comment.attachment_path}`"
             class="img-fluid rounded border"
             style="max-width: 240px;"
           />
           <a
             :href="`/api/comments/${comment.id}/download`"
             target="_blank"
             rel="noopener"
             >
             📎 Скачать файл
           </a>

         </div>
   
         <!-- Кнопка "Ответить" -->
         <button
           class="btn btn-sm btn-outline-primary mt-2"
           @click="showReply = !showReply"
         >
           {{ showReply ? 'Скрыть форму' : 'Ответить' }}
         </button>
   
         <!-- Форма ответа -->
         <comment-form
           v-if="showReply"
           :parent-id="comment.id"
           @comment-added="handleReply"
         />
   
         <!-- Ответы -->
         <div v-if="comment.children?.length" class="mt-3">
           <div class="text-muted mb-1" v-if="level === 0">💬 Ответы:</div>
           <comment-item
             v-for="child in comment.children"
             :key="child.id"
             :comment="child"
             :level="level + 1"
             @comment-added="$emit('comment-added')"
           />
         </div>
       </div>
     </div>
   </template>
   
   <script setup>
   import { ref } from 'vue'
   import CommentForm from './CommentForm.vue'
   import CommentItem from './CommentItem.vue'
   
   const emit = defineEmits(['comment-added'])
   
   defineProps({
     comment: Object,
     level: Number
   })
   
   const showReply = ref(false)
   
   // Безопасный HTML
   function sanitizeHtml(html) {
     const allowed = ['a', 'strong', 'i', 'code']
     const wrapper = document.createElement('div')
     wrapper.innerHTML = html
   
     wrapper.querySelectorAll('*').forEach(el => {
       if (!allowed.includes(el.tagName.toLowerCase())) {
         el.replaceWith(...el.childNodes)
       } else {
         [...el.attributes].forEach(attr => {
           if (!['href', 'title'].includes(attr.name)) {
             el.removeAttribute(attr.name)
           }
         })
       }
     })
   
     return wrapper.innerHTML
   }
   
   function handleReply() {
     showReply.value = false
     emit('comment-added')
   }
   </script>
   
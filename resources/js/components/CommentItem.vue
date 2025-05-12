<template>
     <div :class="['mb-3', `nested-level-${level}`]">
       <div :class="[
         'card p-3',
         level > 0 ? 'bg-light border-start border-primary border-2 ps-4 small' : '',
       ]">
         <h6 class="mb-1">
           {{ comment.user_name }}
           <small class="text-muted">({{ comment.email }})</small>
         </h6>
   
         <p v-html="sanitizeHtml(comment.text)"></p>
   
         <!-- Вложение -->
         <div v-if="comment.attachment_path" class="mt-2">
           <!-- Изображение -->
           <div v-if="comment.attachment_type === 'image'" class="attachment-wrapper">
             <img
               :src="`/storage/${comment.attachment_path}`"
               class="img-fluid rounded border attachment-image"
               style="max-width: 240px;"
             />
             <a
               :href="`/api/comments/${comment.id}/download`"
               class="download-button"
               title="Скачать"
               target="_blank"
               rel="noopener"
             >
               ⬇️
             </a>
           </div>
   
           <!-- Текстовые файлы -->
           <a
             v-else
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
           <transition-group name="fade-child" tag="div">
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
     window.dispatchEvent(new Event('refresh-comments'))
   }
   </script>
   
   <style scoped>
   .fade-child-enter-active,
   .fade-child-leave-active {
     transition: all 0.4s ease;
   }
   .fade-child-enter-from,
   .fade-child-leave-to {
     opacity: 0;
     transform: translateX(12px);
   }
   
   /* Визуальное оформление уровней вложенности */
   .nested-level-1 { margin-left: 10px; }
   .nested-level-2 { margin-left: 20px; }
   .nested-level-3 { margin-left: 30px; }
   .nested-level-4 { margin-left: 40px; }
   .nested-level-5 { margin-left: 50px; }
   
   /* Обертка для вложений */
   .attachment-wrapper {
     position: relative;
     display: inline-block;
     max-width: 240px;
   }
   
   .attachment-image {
     display: block;
     width: 100%;
     border-radius: 6px;
   }
   
   .download-button {
     position: absolute;
     bottom: 10px;
     right: 10px;
     background-color: rgba(0, 0, 0, 0.6);
     color: white;
     padding: 6px 10px;
     font-size: 1rem;
     border-radius: 4px;
     text-decoration: none;
     opacity: 0;
     transition: opacity 0.3s ease;
   }
   
   .attachment-wrapper:hover .download-button {
     opacity: 1;
   }
   </style>
   
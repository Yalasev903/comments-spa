<template>
     <div class="mt-5">
       <h3 class="mb-3">Список комментариев</h3>
   
       <div v-if="loading">Загрузка...</div>
   
       <transition-group name="fade" tag="div" v-else>
         <comment-item
           v-for="comment in comments.data"
           :key="comment.id"
           :comment="comment"
           :level="0"
           @comment-added="refreshTree"
         />
       </transition-group>
     </div>
   </template>
   
   <script setup>
   import { ref, watch, onMounted, onUnmounted } from 'vue'
   import axios from 'axios'
   import CommentItem from './CommentItem.vue'
   
   const props = defineProps({
     reloadKey: Number
   })
   
   const comments = ref({ data: [] })
   const loading = ref(true)
   
   async function loadComments() {
     loading.value = true
     try {
       const response = await axios.get('/api/comments')
       comments.value = response.data
     } catch (error) {
       console.error('Ошибка загрузки комментариев:', error)
     } finally {
       loading.value = false
     }
   }
   
   // Позволяет также перезагружать из дочерних компонентов
   function refreshTree() {
     loadComments()
   }
   
   // При первой загрузке
   watch(() => props.reloadKey, loadComments, { immediate: true })
   
   // Обновление при добавлении комментариев в любом уровне
   onMounted(() => {
     window.addEventListener('refresh-comments', refreshTree)
   })
   
   onUnmounted(() => {
     window.removeEventListener('refresh-comments', refreshTree)
   })
   </script>
   
   <style scoped>
   .fade-enter-active,
   .fade-leave-active {
     transition: all 0.4s ease;
   }
   .fade-enter-from,
   .fade-leave-to {
     opacity: 0;
     transform: translateY(10px);
   }
   </style>
   
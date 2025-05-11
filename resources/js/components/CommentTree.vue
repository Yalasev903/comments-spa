<template>
     <div class="mt-5">
       <h3 class="mb-3">Список комментариев</h3>
   
       <div v-if="loading">Загрузка...</div>
       <div v-else>
         <comment-item
           v-for="comment in comments.data"
           :key="comment.id"
           :comment="comment"
           :level="0"
           @comment-added="refreshTree"
         />
       </div>
     </div>
   </template>
   
   <script setup>
   import { ref, watch } from 'vue'
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
   
   watch(() => props.reloadKey, loadComments, { immediate: true })
   </script>
   
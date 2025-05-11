<template>
     <div class="container mt-5">
       <h1 class="text-center mb-4">Приложение комментариев</h1>
       <comment-form @comment-added="refreshComments" />
       <comment-tree :reload-key="reloadKey" />
     </div>
   </template>
   
   <script setup>
   import CommentForm from './components/CommentForm.vue'
   import CommentTree from './components/CommentTree.vue'
   import { ref } from 'vue'
   
   const reloadKey = ref(0)
   function refreshComments() {
     reloadKey.value++
   }
   </script>
   
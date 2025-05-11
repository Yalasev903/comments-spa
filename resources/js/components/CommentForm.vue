<template>
     <div class="card p-4 mt-4 shadow-sm">
       <h2 class="mb-3">{{ parentId ? 'Ответить на комментарий' : 'Добавить комментарий' }}</h2>
   
       <form @submit.prevent="submit">
         <!-- Имя -->
         <div class="mb-3">
           <label class="form-label">Имя *</label>
           <input v-model="form.user_name" type="text" class="form-control" required />
           <div class="text-danger" v-if="errors.user_name">{{ errors.user_name[0] }}</div>
         </div>
   
         <!-- Email -->
         <div class="mb-3">
           <label class="form-label">Email *</label>
           <input v-model="form.email" type="email" class="form-control" required />
           <div class="text-danger" v-if="errors.email">{{ errors.email[0] }}</div>
         </div>
   
         <!-- Home Page -->
         <div class="mb-3">
           <label class="form-label">Домашняя страница</label>
           <input v-model="form.home_page" type="url" class="form-control" />
           <div class="text-danger" v-if="errors.home_page">{{ errors.home_page[0] }}</div>
         </div>
   
         <!-- Комментарий -->
         <div class="mb-3">
           <label class="form-label">Комментарий *</label>
           <textarea v-model="form.text" class="form-control" rows="4" required></textarea>
           <div class="text-danger" v-if="errors.text">{{ errors.text[0] }}</div>
         </div>
   
         <!-- CAPTCHA -->
         <div class="mb-3">
           <label class="form-label">Введите: <strong>{{ captchaText }}</strong></label>
           <input v-model="form.captcha" type="text" class="form-control" required />
           <div class="text-danger" v-if="errors.captcha">{{ errors.captcha[0] }}</div>
         </div>
   
         <!-- Файл -->
         <div class="mb-3">
           <label class="form-label">Файл (JPG/PNG/GIF или TXT)</label>
           <input type="file" @change="handleFile" class="form-control" />
           <div v-if="preview" class="mt-2">
             <strong>Превью:</strong><br />
             <img v-if="isImage" :src="preview" class="img-fluid mt-1" style="max-width: 320px;" />
             <pre v-else class="bg-light p-2">{{ fileName }}</pre>
           </div>
         </div>
   
         <!-- Общая ошибка -->
         <div v-if="errors.general" class="alert alert-danger mt-3">
           {{ errors.general[0] }}
         </div>
   
         <!-- Кнопка -->
         <button class="btn btn-primary" :disabled="submitting">Отправить</button>
       </form>
   
       <!-- Успешное сообщение -->
       <div v-if="successMessage" class="alert alert-success mt-3">
         {{ successMessage }}
       </div>
     </div>
   </template>
   
   <script setup>
   import { ref, defineProps, defineEmits, watch } from 'vue'
   import axios from 'axios'
   
   const props = defineProps({
     parentId: {
       type: Number,
       default: null
     }
   })
   
   const emit = defineEmits(['comment-added'])
   
   const form = ref({
     user_name: '',
     email: '',
     home_page: '',
     text: '',
     captcha: '',
     attachment: null,
     parent_id: props.parentId
   })
   
   watch(
     () => props.parentId,
     (newVal) => {
       form.value.parent_id = newVal
     }
   )
   
   const errors = ref({})
   const submitting = ref(false)
   const successMessage = ref('')
   const preview = ref(null)
   const fileName = ref('')
   const isImage = ref(false)
   const captchaText = ref(generateCaptcha())
   
   function generateCaptcha() {
     const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
     return Array.from({ length: 6 }, () => chars[Math.floor(Math.random() * chars.length)]).join('')
   }
   
   function handleFile(event) {
     const file = event.target.files[0]
     if (!file) return
   
     form.value.attachment = file
     fileName.value = file.name
     isImage.value = file.type.startsWith('image/')
   
     if (isImage.value) {
       const reader = new FileReader()
       reader.onload = e => {
         preview.value = e.target.result
       }
       reader.readAsDataURL(file)
     } else {
       preview.value = 'Текстовый файл выбран: ' + file.name
     }
   }
   
   async function submit() {
     submitting.value = true
     errors.value = {}
     successMessage.value = ''
   
     if (form.value.captcha !== captchaText.value) {
       errors.value.captcha = ['Неверная CAPTCHA']
       submitting.value = false
       return
     }
   
     const payload = new FormData()
     for (const key in form.value) {
       if (form.value[key]) {
         payload.append(key, form.value[key])
       }
     }
   
     try {
       const { data } = await axios.post('/api/comments', payload, {
         headers: {
           'Content-Type': 'multipart/form-data',
         },
       })
   
       successMessage.value = data.message
       emit('comment-added')
   
       form.value = {
         user_name: '',
         email: '',
         home_page: '',
         text: '',
         captcha: '',
         attachment: null,
         parent_id: props.parentId
       }
       preview.value = null
       fileName.value = ''
       isImage.value = false
       captchaText.value = generateCaptcha()
     } catch (err) {
       console.error('❌ Ошибка отправки:', err)
   
       if (err.response?.status === 422) {
         errors.value = err.response.data.errors
       } else if (err.response?.status === 500) {
         errors.value.general = ['Ошибка сервера: ' + (err.response.data.error || '500')]
       } else {
         errors.value.general = ['Неизвестная ошибка. Проверь консоль браузера.']
       }
     } finally {
       submitting.value = false
     }
   }
   </script>
   
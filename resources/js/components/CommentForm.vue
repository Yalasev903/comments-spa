
<template>
     <div class="mt-3">
       <button v-if="!parentId && !showForm" class="btn btn-sm btn-outline-primary" @click="showForm = true">
         ➕ Добавить комментарий
       </button>
   
       <transition name="fade-scale">
         <div v-if="parentId || showForm" class="card border shadow-sm p-3 mt-3">
           <h6 class="mb-3 fw-bold">
             {{ parentId ? 'Ответить на комментарий' : 'Добавить комментарий' }}
           </h6>
           <form @submit.prevent="submit" class="small">

               <div class="mb-2">
               <label class="form-label mb-1">Имя *</label>
               <input
                 v-model="form.user_name"
                 type="text"
                 class="form-control form-control-sm"
                 required
                 pattern="^[a-zA-Z0-9]+$"
               />
               <div class="text-danger small" v-if="errors.user_name">{{ errors.user_name[0] }}</div>
             </div>
       
             <div class="mb-2">
               <label class="form-label mb-1">Email *</label>
               <input v-model="form.email" type="email" class="form-control form-control-sm" required />
               <div class="text-danger small" v-if="errors.email">{{ errors.email[0] }}</div>
             </div>
       
             <div class="mb-2">
               <label class="form-label mb-1">Домашняя страница</label>
               <input v-model="form.home_page" type="url" class="form-control form-control-sm" />
               <div class="text-danger small" v-if="errors.home_page">{{ errors.home_page[0] }}</div>
             </div>
       
             <div class="mb-2 d-flex gap-2 flex-wrap">
               <button type="button" class="btn btn-sm btn-outline-secondary" @click="insertTag('<strong>', '</strong>')"><b>B</b></button>
               <button type="button" class="btn btn-sm btn-outline-secondary" @click="insertTag('<i>', '</i>')"><i>I</i></button>
               <button type="button" class="btn btn-sm btn-outline-secondary" @click="insertTag('<code>', '</code>')">Code</button>
               <button type="button" class="btn btn-sm btn-outline-secondary" @click="insertTag('<a href=\'https://\'>', '</a>')">Link</button>
             </div>
       
             <div class="mb-2">
               <label class="form-label mb-1">Комментарий *</label>
               <textarea v-model="form.text" class="form-control form-control-sm" rows="3" required></textarea>
               <div class="text-danger small" v-if="errors.text">{{ errors.text[0] }}</div>
             </div>
       
             <div v-if="form.text" class="alert alert-light border small mb-2">
               <strong>Предпросмотр:</strong>
               <div v-html="sanitizeHtml(form.text)"></div>
             </div>
       
             <!-- CAPTCHA -->
             <div class="mb-2">
               <label class="form-label mb-1">Введите: <strong>{{ captchaText }}</strong></label>
               <input v-model="form.captcha" type="text" class="form-control form-control-sm" required />
               <div class="text-danger small" v-if="errors.captcha">{{ errors.captcha[0] }}</div>
             </div>
       
             <div class="mb-2">
               <label class="form-label mb-1">Файл (JPG/PNG/GIF или TXT)</label>
               <input type="file" @change="handleFile" class="form-control form-control-sm" />
               <div v-if="preview" class="mt-2">
                 <strong class="small">Превью:</strong><br />
                 <img v-if="isImage" :src="preview" class="img-fluid rounded mt-1" style="max-width: 200px;" />
                 <pre v-else class="bg-light p-2 small">{{ fileName }}</pre>
               </div>
             </div>
       
             <div v-if="errors.general" class="alert alert-danger mt-2 small">
               {{ errors.general[0] }}
             </div>
       
             <button class="btn btn-sm btn-primary mt-1" :disabled="submitting">Отправить</button>
           </form>
       
           <div v-if="successMessage" class="alert alert-success mt-2 small">
             {{ successMessage }}
           </div>
         </div>
       </transition>
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
   
   const showForm = ref(false)
   
   const parentId = ref(props.parentId)
   watch(() => props.parentId, newVal => {
     parentId.value = newVal
   })
   
   const form = ref({
     user_name: '',
     email: '',
     home_page: '',
     text: '',
     captcha: '',
     attachment: null,
     parent_id: parentId.value
   })
   
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
   
   function insertTag(openTag, closeTag) {
     const textarea = document.querySelector('textarea')
     const start = textarea.selectionStart
     const end = textarea.selectionEnd
     const text = form.value.text
   
     form.value.text =
       text.substring(0, start) + openTag + text.substring(start, end) + closeTag + text.substring(end)
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
           if (!['href', 'title'].includes(attr.name)) {
             el.removeAttribute(attr.name)
           }
           if (el.tagName.toLowerCase() === 'a' && !el.getAttribute('href')?.startsWith('http')) {
             el.removeAttribute('href')
           }
         })
       }
     })
   
     return wrapper.innerHTML
   }
   
   async function submit() {
     submitting.value = true
     errors.value = {}
     successMessage.value = ''
   
     form.value.parent_id = parentId.value
   
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
           'Content-Type': 'multipart/form-data'
         }
       })
   
       successMessage.value = data.message
       emit('comment-added')
   
       form.value.user_name = ''
       form.value.email = ''
       form.value.home_page = ''
       form.value.text = ''
       form.value.captcha = ''
       form.value.attachment = null
       form.value.parent_id = parentId.value
       preview.value = null
       fileName.value = ''
       isImage.value = false
       captchaText.value = generateCaptcha()
     } catch (err) {
       console.error('❌ Ошибка отправки:', err)
       console.log('🪵 Ответ от API:', err.response?.data)
   
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
   
   <style scoped>
   .fade-scale-enter-active,
   .fade-scale-leave-active {
     transition: all 0.5s ease-in-out;
   }
   .fade-scale-enter-from,
   .fade-scale-leave-to {
     opacity: 0;
     transform: scale(0.98);
   }
   
   /* Мобильная адаптация */
@media (max-width: 576px) {
  .form-control,
  .form-label,
  textarea,
  input,
  .btn,
  .text-muted,
  .alert {
    font-size: 1.2rem !important;
    line-height: 1.4;
  }

  .form-control,
  textarea,
  input {
    padding: 0.75rem 1rem;
  }

  .form-label {
    margin-bottom: 0.25rem;
  }

  .btn {
    padding: 0.75rem 1.2rem;
    font-weight: 600;
    width: 100%;
    border-radius: 0.4rem;
  }

  .card {
    padding: 1.25rem !important;
    border-radius: 0.75rem;
  }

  .mb-2 {
    margin-bottom: 1.25rem !important;
  }

  .alert {
    padding: 1rem;
  }
   }
   </style>
   
   
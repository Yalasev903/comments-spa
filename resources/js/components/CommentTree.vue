<template>
  <div class="mt-4">
    <h2 class="mb-3" ref="treeTop">💬 Комментарии</h2>

    <!-- Блок сортировки -->
    <div class="d-flex gap-2 align-items-center mb-3 flex-wrap">
      <label class="fw-normal">Сортировка:</label>
      <select v-model="sortBy" class="form-select form-select-sm w-auto">
        <option value="created_at">Дата</option>
        <option value="user_name">Имя</option>
        <option value="email">Email</option>
      </select>
      <select v-model="sortOrder" class="form-select form-select-sm w-auto">
        <option value="desc">По убыванию</option>
        <option value="asc">По возрастанию</option>
      </select>
    </div>

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

    <div v-if="comments.last_page && comments.last_page > 1" class="d-flex justify-content-center mt-4">
      <nav>
        <ul class="pagination pagination-sm mb-0 flex-wrap">
          <li class="page-item" :class="{ disabled: comments.current_page === 1 }">
            <button class="page-link" @click="goToPage(comments.current_page - 1)" :disabled="comments.current_page === 1">←</button>
          </li>

          <li
            v-for="page in pagesToShow"
            :key="page"
            class="page-item"
            :class="{ active: page === comments.current_page }"
          >
            <button class="page-link" @click="goToPage(page)">{{ page }}</button>
          </li>

          <li class="page-item" :class="{ disabled: comments.current_page === comments.last_page }">
            <button class="page-link" @click="goToPage(comments.current_page + 1)" :disabled="comments.current_page === comments.last_page">→</button>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, computed } from 'vue'
import axios from 'axios'
import CommentItem from './CommentItem.vue'

const props = defineProps({
  reloadKey: Number
})

const comments = ref({ data: [] })
const loading = ref(true)
const currentPage = ref(1)
const treeTop = ref(null)

const sortBy = ref('created_at')
const sortOrder = ref('desc')

async function loadComments(page = 1) {
  loading.value = true
  try {
    const response = await axios.get('/api/comments', {
      params: {
        page,
        sort_by: sortBy.value,
        order: sortOrder.value
      }
    })
    comments.value = response.data
    currentPage.value = response.data.current_page
    scrollToTop()
  } catch (error) {
    console.error('Ошибка загрузки комментариев:', error)
  } finally {
    loading.value = false
  }
}

function scrollToTop() {
  if (treeTop.value) {
    treeTop.value.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

function refreshTree() {
  loadComments(currentPage.value)
}

function goToPage(page) {
  if (page < 1 || (comments.value.last_page && page > comments.value.last_page)) return
  loadComments(page)
}

const pagesToShow = computed(() => {
  if (!comments.value.last_page) return []
  const current = comments.value.current_page
  const last = comments.value.last_page
  const delta = 2
  const pages = []
  for (let i = Math.max(1, current - delta); i <= Math.min(last, current + delta); i++) {
    pages.push(i)
  }
  return pages
})

watch(() => props.reloadKey, () => loadComments(currentPage.value), { immediate: true })

watch([sortBy, sortOrder], () => loadComments(1))

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
  transition: all 0.5s ease-in-out;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(12px);
}
/* Адаптивность пагинации */
@media (max-width: 576px) {
  .pagination {
    justify-content: center;
    flex-wrap: wrap;
  }
  .pagination .page-link {
    padding: 0.3rem 0.6rem;
    font-size: 0.85rem;
  }
}
</style>

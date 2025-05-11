import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'
import { createApp } from 'vue'
import App from './App.vue'
import CommentForm from './components/CommentForm.vue'
import CommentTree from './components/CommentTree.vue'

const app = createApp(App)
app.component('comment-form', CommentForm)
app.component('comment-tree', CommentTree)
app.mount('#app')

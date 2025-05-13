import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'

import Echo from 'laravel-echo'
import io from 'socket.io-client'
import { createApp } from 'vue'
import App from './App.vue'
import CommentForm from './components/CommentForm.vue'
import CommentTree from './components/CommentTree.vue'


window.io = io
window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: window.location.hostname + ':6001',
    transports: ['websocket'],
    enabledTransports: ['ws'],
    forceTLS: false,
    disableStats: true,
    reconnectionAttempts: 5,
    reconnectionDelay: 2000,
})

window.Echo.channel('comments')
    .listen('.comment.created', (e) => {
        console.log('📡 Новый комментарий по WebSocket:', e.comment)
    })

const app = createApp(App)
app.component('comment-form', CommentForm)
app.component('comment-tree', CommentTree)
app.mount('#app')

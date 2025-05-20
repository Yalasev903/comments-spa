import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'

import Echo from 'laravel-echo'
import io from 'socket.io-client'
import { createApp } from 'vue'
import App from './App.vue'
import CommentForm from './components/CommentForm.vue'
import CommentTree from './components/CommentTree.vue'

window.io = io

// В локальной .env: VITE_ECHO_HOST=127.0.0.1:6001

let echoHost = import.meta.env.VITE_ECHO_HOST
if (!echoHost) {
    echoHost = window.location.hostname + ':6001'
}
const echoScheme = window.location.protocol === 'https:' ? 'wss' : 'ws'
const fullHost = `${echoScheme}://${echoHost}`

console.log('🌐 VITE_ECHO_HOST:', echoHost)
console.log('🔌 Подключение к WebSocket:', fullHost)

window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: fullHost,
    path: '/socket.io',
    transports: ['websocket'],
    enabledTransports: ['ws'],
    forceTLS: echoScheme === 'wss',
    disableStats: true,
    reconnectionAttempts: 5,
    reconnectionDelay: 2000,
})

// События подключения сокета
if (window.Echo.connector && window.Echo.connector.socket) {
    window.Echo.connector.socket.on('connect', () => {
        console.log('✅ WebSocket подключён')
    })

    window.Echo.connector.socket.on('disconnect', () => {
        console.warn('❌ WebSocket отключён')
    })

    window.Echo.connector.socket.on('error', (error) => {
        console.error('⚠ Ошибка WebSocket:', error)
    })

    window.Echo.connector.socket.on('reconnect_attempt', () => {
        console.log('🔁 Повторная попытка подключения к WebSocket...')
    })
} else {
    console.warn('⚠ Echo.connector.socket не инициализирован — WebSocket не подключён!')
}

// Подписка на канал и прослушка события
window.Echo.channel('comments')
    .listen('.comment.created', (e) => {
        console.log('📡 Новый комментарий по WebSocket:', e)
        window.dispatchEvent(new Event('refresh-comments'))
    })

// Создание Vue приложения
const app = createApp(App)
app.component('comment-form', CommentForm)
app.component('comment-tree', CommentTree)
app.mount('#app')

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

console.log('Pusher:', typeof Pusher);  // Debug log to ensure Pusher is loaded

Pusher.logToConsole = true;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: false,  // Set this to true in production for secure connections
    enabledTransports: ['ws'],
    authEndpoint: "/broadcasting/auth",
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }
});

console.log('Echo initialized:', window.Echo);  // Debug log to ensure Echo is initialized

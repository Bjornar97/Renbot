import Echo from "laravel-echo";
import Pusher from "pusher-js";
import axios from "axios";

// @ts-ignore
window.Pusher = Pusher;

export const websocket = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_BROADCASTING_APP_KEY,
    wsHost: import.meta.env.VITE_BROADCASTING_FRONTEND_HOST,
    wsPort: import.meta.env.VITE_BROADCASTING_PORT,
    wssPort: import.meta.env.VITE_BROADCASTING_PORT,
    forceTLS: false,
    encrypted: true,
    disableStats: true,
    enabledTransports: ["ws", "wss"],
    cluster: import.meta.env.VITE_BROADCASTING_APP_CLUSTER, //added this line
});

axios.interceptors.request.use(function (config) {
    config.headers["X-Socket-ID"] = websocket.socketId();
    return config;
});

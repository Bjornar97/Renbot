import Echo from "laravel-echo";
import Pusher from "pusher-js";
import axios from "axios";

// @ts-ignore
window.Pusher = Pusher;

export const websocket = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],
});

axios.interceptors.request.use(function (config) {
    config.headers["X-Socket-ID"] = websocket.socketId();
    return config;
});

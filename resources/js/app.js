import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';

const app = createApp(App);

// Initialize Pinia
const pinia = createPinia();
app.use(pinia);

// Initialize Router
app.use(router);

app.mount('#app');
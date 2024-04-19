import {createApp} from 'vue'
import App from './App.vue'
import PrimeVue from 'primevue/config';
import Tooltip from 'primevue/tooltip';
import ToastService from 'primevue/toastservice';
import {FORM_MODE, KEY_CODE, MESSAGE} from '@/common/enums';
import './registerServiceWorker'
import router from './router'
import store from './store'

const app = createApp(App);
app.directive('tooltip', Tooltip);
app.config.globalProperties.FormMode = FORM_MODE;
app.config.globalProperties.KeyCode = KEY_CODE;
app.config.globalProperties.Message = MESSAGE;
export function handleServerError() {
    app.config.globalProperties.$toast.add({ severity: 'error', summary: 'Thông báo', detail: 'Có lỗi xảy ra, vui lòng liên hệ nhà phát triển.', life: 3000 });
}

export function handleSuccess({message}: { message: any }) {
    app.config.globalProperties.$toast.add({ severity: 'success', summary: 'Thông báo', detail: message, life: 3000 });
}
export function handleError({message}: { message: any }) {
    app.config.globalProperties.$toast.add({ severity: 'error', summary: 'Thông báo', detail: message, life: 3000 });
}

export function handleWarning({message}: { message: any }) {
    app.config.globalProperties.$toast.add({ severity: 'warn', summary: 'Thông báo', detail: message, life: 3000 });
}

app.use(store)
    .use(PrimeVue)
    .use(router)
    .use(ToastService)
    .mount('#app')


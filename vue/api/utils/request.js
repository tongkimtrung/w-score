import axios from 'axios'
import store from '../../src/store'
import router from '@/router'
import Auth from "./auth";
import {RESPONSE_STATUS} from "@/common/enums";

const service = axios.create({
    baseURL: "http://localhost:9000/", // uri = baseURL + apiFunction truyền tới
    timeout: 50000,
    withCredentials: true,
    headers: { // Request Headers
        'content-type': 'application/json',
        'Accept': 'application/json'
    }
})

service.interceptors.request.use(
    config => {
        if (Auth.check()) {
            config.headers['Authorization'] = 'Bearer ' + Auth.getToken()
        }
        return config
    },
    error => {
        console.log('error', error)
    }
)

service.interceptors.response.use(
    response => {
        const res = response.data
        return res
    },
    error => {
        console.log(error)
        if (error.response) {
            // lỗi đăng nhập thực hiện redirect về trang đăng nhập
            if (error.request.status === RESPONSE_STATUS.HTTP_UNAUTHORIZED) {
                Auth.logout();
                router.push('/login')
            } else if (error.request.status !== RESPONSE_STATUS.HTTP_UNPROCESSABLE_ENTITY)
                store.dispatch('handleServerError');
        }
        else store.dispatch('handleServerError');
        return Promise.reject(error)
    }
)

export default service

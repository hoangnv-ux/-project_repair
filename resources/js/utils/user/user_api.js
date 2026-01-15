import axios from 'axios'
import router from '@/router/user'
import { useAuthStore } from '@/stores/user/auth'

function createUserAxios() {
    const auth = useAuthStore()

    const instance = axios.create({
        baseURL: '/api/',
        headers: {
            'Accept': 'application/json',
        },
    })

    instance.interceptors.request.use(config => {
        if (!auth.token) {
            router.push('/login')
            throw new axios.Cancel('User not authenticated')
        }

        if (!config.headers['Content-Type']) {
            const isFormData = config.data instanceof FormData
            config.headers['Content-Type'] = isFormData ? 'multipart/form-data' : 'application/json'
        }

        config.headers.Authorization = `Bearer ${auth.token}`
        return config
    })

    instance.interceptors.response.use(
        response => response,
        error => {
            if (error.response?.status === 401) {
                auth.logout()
                router.push('/login')
            }

            return Promise.reject(error)
        }
    )

    return instance
}

export const userApi = {
    get: (url, config = {}) => createUserAxios().get(url, config),
    post: (url, data = {}, config = {}) => createUserAxios().post(url, data, config),
    put: (url, data = {}, config = {}) => createUserAxios().put(url, data, config),
    delete: (url, config = {}) => createUserAxios().delete(url, config),
}

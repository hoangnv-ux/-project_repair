import axios from 'axios'
import router from '@/router/admin'
import { useAuthStore } from '@/stores/admin/auth'

function createAdminAxios() {
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

export const adminApi = {
    get: (url, config = {}) => createAdminAxios().get(url, config),
    post: (url, data = {}, config = {}) => createAdminAxios().post(url, data, config),
    put: (url, data = {}, config = {}) => createAdminAxios().put(url, data, config),
    delete: (url, config = {}) => createAdminAxios().delete(url, config),
}

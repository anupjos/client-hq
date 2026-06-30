import axios from 'axios'

const baseURL = import.meta.env.VITE_API_URL
if (!baseURL) {
  throw new Error('VITE_API_URL is not set.')
}

const TOKEN_STORAGE_KEY = 'clienthq.token'

export const tokenStore = {
  get: () => localStorage.getItem(TOKEN_STORAGE_KEY),
  set: (token) => localStorage.setItem(TOKEN_STORAGE_KEY, token),
  clear: () => localStorage.removeItem(TOKEN_STORAGE_KEY),
}

const api = axios.create({
  baseURL,
  headers: { Accept: 'application/json' },
})

api.interceptors.request.use((config) => {
  const token = tokenStore.get()
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

let onUnauthorized = null
export const setOnUnauthorized = (handler) => {
  onUnauthorized = handler
}

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      tokenStore.clear()
      onUnauthorized?.()
    }
    return Promise.reject(error)
  },
)

export default api

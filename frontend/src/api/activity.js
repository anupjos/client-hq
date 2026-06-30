import api from './client'

export const listActivity = () => api.get('/activity').then((r) => r.data)

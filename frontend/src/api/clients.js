import api from './client'

export const listClients = () => api.get('/clients').then((r) => r.data)

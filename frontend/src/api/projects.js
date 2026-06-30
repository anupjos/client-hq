import api from './client'

export const listProjects = () => api.get('/projects').then((r) => r.data)

export const getProject = (id) => api.get(`/projects/${id}`).then((r) => r.data)

export const createProject = (payload) =>
  api.post('/projects', payload).then((r) => r.data)

export const updateProject = (id, payload) =>
  api.patch(`/projects/${id}`, payload).then((r) => r.data)

export const deleteProject = (id) =>
  api.delete(`/projects/${id}`).then((r) => r.data)

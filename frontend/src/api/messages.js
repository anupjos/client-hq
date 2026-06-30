import api from './client'

export const listMessages = (projectId) =>
  api.get(`/projects/${projectId}/messages`).then((r) => r.data)

export const sendMessage = (projectId, content) =>
  api
    .post(`/projects/${projectId}/messages`, { content })
    .then((r) => r.data)

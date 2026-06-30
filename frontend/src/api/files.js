import api from './client'

export const listFiles = (projectId) =>
  api.get(`/projects/${projectId}/files`).then((r) => r.data)

export const uploadFile = (projectId, file) => {
  const form = new FormData()
  form.append('file', file)
  return api
    .post(`/projects/${projectId}/files`, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    .then((r) => r.data)
}

export const downloadFile = async (projectId, file) => {
  const response = await api.get(
    `/projects/${projectId}/files/${file.id}`,
    { responseType: 'blob' },
  )
  const url = URL.createObjectURL(response.data)
  const a = document.createElement('a')
  a.href = url
  a.download = file.original_name
  document.body.appendChild(a)
  a.click()
  a.remove()
  URL.revokeObjectURL(url)
}

export const deleteFile = (projectId, fileId) =>
  api.delete(`/projects/${projectId}/files/${fileId}`).then((r) => r.data)

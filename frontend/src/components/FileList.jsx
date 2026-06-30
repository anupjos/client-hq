import { useRef, useState } from 'react'
import { deleteFile, downloadFile, uploadFile } from '../api/files'

function formatSize(bytes) {
  if (!bytes && bytes !== 0) return ''
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

export default function FileList({ projectId, files, canDelete, onChange }) {
  const inputRef = useRef(null)
  const [uploading, setUploading] = useState(false)
  const [error, setError] = useState(null)

  const handleUpload = async (e) => {
    const file = e.target.files?.[0]
    if (!file) return
    setError(null)
    setUploading(true)
    try {
      await uploadFile(projectId, file)
      onChange()
    } catch (err) {
      setError(err.response?.data?.message || 'Upload failed.')
    } finally {
      setUploading(false)
      if (inputRef.current) inputRef.current.value = ''
    }
  }

  const handleDelete = async (file) => {
    if (!confirm(`Delete ${file.original_name}?`)) return
    try {
      await deleteFile(projectId, file.id)
      onChange()
    } catch (err) {
      setError(err.response?.data?.message || 'Delete failed.')
    }
  }

  return (
    <div className="file-list">
      <div className="actions">
        <input
          ref={inputRef}
          type="file"
          onChange={handleUpload}
          disabled={uploading}
        />
        {uploading && <span className="muted small">Uploading…</span>}
      </div>

      {error && <p className="error">{error}</p>}

      {files.length === 0 ? (
        <p className="muted">No files yet.</p>
      ) : (
        <ul className="file-rows">
          {files.map((f) => (
            <li key={f.id} className="file-row">
              <button
                type="button"
                className="link"
                onClick={() => downloadFile(projectId, f)}
              >
                {f.original_name}
              </button>
              <span className="muted small">{formatSize(f.size)}</span>
              {canDelete && (
                <button
                  type="button"
                  className="ghost small"
                  onClick={() => handleDelete(f)}
                >
                  Delete
                </button>
              )}
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}

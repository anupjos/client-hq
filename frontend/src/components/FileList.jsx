import { useRef, useState } from 'react'
import { deleteFile, downloadFile, uploadFile } from '../api/files'

function TrashIcon() {
  return (
    <svg
      width="13"
      height="13"
      viewBox="0 0 16 16"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.6"
      strokeLinecap="round"
      strokeLinejoin="round"
      aria-hidden="true"
    >
      <path d="M3 5h10" />
      <path d="M6 5V3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2" />
      <path d="M4.2 5l.6 8a1 1 0 0 0 1 .9h4.4a1 1 0 0 0 1-.9l.6-8" />
      <path d="M7 8v3M9 8v3" />
    </svg>
  )
}

function UploadIcon() {
  return (
    <svg
      width="13"
      height="13"
      viewBox="0 0 16 16"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.7"
      strokeLinecap="round"
      strokeLinejoin="round"
      aria-hidden="true"
    >
      <path d="M8 11V3" />
      <path d="M5 6l3-3 3 3" />
      <path d="M3 12v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1" />
    </svg>
  )
}

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
      <input
        ref={inputRef}
        type="file"
        onChange={handleUpload}
        disabled={uploading}
        hidden
      />

      {files.length === 0 ? (
        <p className="muted small">No files yet.</p>
      ) : (
        <ul className="file-rows">
          {files.map((f) => (
            <li key={f.id} className="file-row">
              <button
                type="button"
                className="link file-name"
                onClick={() => downloadFile(projectId, f)}
                title={f.original_name}
              >
                {f.original_name}
              </button>
              <span className="muted small">{formatSize(f.size)}</span>
              {canDelete && (
                <button
                  type="button"
                  className="icon-button danger"
                  onClick={() => handleDelete(f)}
                  aria-label={`Delete ${f.original_name}`}
                  title="Delete file"
                >
                  <TrashIcon />
                </button>
              )}
            </li>
          ))}
        </ul>
      )}

      {error && <p className="error">{error}</p>}

      <button
        type="button"
        className="ghost small with-icon upload-trigger"
        onClick={() => inputRef.current?.click()}
        disabled={uploading}
      >
        <UploadIcon />
        {uploading ? 'Uploading…' : 'Upload file'}
      </button>
    </div>
  )
}

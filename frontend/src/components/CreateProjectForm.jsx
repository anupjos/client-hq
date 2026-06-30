import { useEffect, useState } from 'react'
import { listClients } from '../api/clients'
import { createProject } from '../api/projects'

export default function CreateProjectForm({ onCreated, onCancel }) {
  const [clients, setClients] = useState([])
  const [clientId, setClientId] = useState('')
  const [name, setName] = useState('')
  const [notes, setNotes] = useState('')
  const [error, setError] = useState(null)
  const [submitting, setSubmitting] = useState(false)

  useEffect(() => {
    listClients()
      .then((list) => {
        setClients(list)
        if (list.length > 0) setClientId(String(list[0].id))
      })
      .catch(() => setError('Failed to load clients.'))
  }, [])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError(null)
    setSubmitting(true)
    try {
      const project = await createProject({
        client_id: Number(clientId),
        name,
        notes: notes || null,
      })
      onCreated(project)
    } catch (err) {
      const data = err.response?.data
      setError(
        data?.errors
          ? Object.values(data.errors).flat().join(' ')
          : data?.message || 'Could not create project.',
      )
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <form className="card form-stack" onSubmit={handleSubmit}>
      <h2>New project</h2>

      <label>
        Client
        <select
          required
          value={clientId}
          onChange={(e) => setClientId(e.target.value)}
        >
          {clients.length === 0 && <option value="">No clients yet</option>}
          {clients.map((c) => (
            <option key={c.id} value={c.id}>
              {c.name} ({c.email})
            </option>
          ))}
        </select>
      </label>

      <label>
        Name
        <input
          type="text"
          required
          value={name}
          onChange={(e) => setName(e.target.value)}
        />
      </label>

      <label>
        Notes (optional)
        <textarea
          rows={4}
          value={notes}
          onChange={(e) => setNotes(e.target.value)}
        />
      </label>

      {error && <p className="error">{error}</p>}

      <div className="actions">
        <button type="button" className="ghost" onClick={onCancel}>
          Cancel
        </button>
        <button type="submit" disabled={submitting || !clientId}>
          {submitting ? 'Creating…' : 'Create project'}
        </button>
      </div>
    </form>
  )
}

import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import ChatPanel from '../components/ChatPanel'
import FileList from '../components/FileList'
import StatusBadge from '../components/StatusBadge'
import Topbar from '../components/Topbar'
import { getProject } from '../api/projects'
import { useAuth } from '../hooks/useAuth'

export default function ProjectPage() {
  const { id } = useParams()
  const { user } = useAuth()
  const [project, setProject] = useState(null)
  const [error, setError] = useState(null)

  const reload = () => {
    getProject(id)
      .then(setProject)
      .catch((err) =>
        setError(err.response?.data?.message || 'Failed to load project.'),
      )
  }

  useEffect(() => {
    reload()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id])

  const isAdmin = user.role === 'admin'

  return (
    <div className="page">
      <Topbar />
      <main className="container">
        <Link to="/" className="back-link">← Projects</Link>

        {error && <p className="error">{error}</p>}
        {!project && !error && <p className="muted">Loading…</p>}

        {project && (
          <>
            <div className="page-header">
              <h1>{project.name}</h1>
              <StatusBadge status={project.status} />
            </div>

            {isAdmin && project.client && (
              <p className="muted">Client: {project.client.name} ({project.client.email})</p>
            )}

            {project.notes && (
              <section className="section">
                <h2>Notes</h2>
                <pre className="notes">{project.notes}</pre>
              </section>
            )}

            <section className="section">
              <h2>Files</h2>
              <FileList
                projectId={project.id}
                files={project.files || []}
                canDelete={isAdmin}
                onChange={reload}
              />
            </section>

            <section className="section">
              <h2>AI assistant</h2>
              <ChatPanel projectId={project.id} />
            </section>
          </>
        )}
      </main>
    </div>
  )
}

import { useEffect, useState } from 'react'
import CreateProjectForm from '../components/CreateProjectForm'
import ProjectCard from '../components/ProjectCard'
import Topbar from '../components/Topbar'
import { listProjects } from '../api/projects'
import { useAuth } from '../hooks/useAuth'

export default function DashboardPage() {
  const { user } = useAuth()
  const [projects, setProjects] = useState(null)
  const [error, setError] = useState(null)
  const [creating, setCreating] = useState(false)

  const reload = () => {
    listProjects()
      .then(setProjects)
      .catch((err) =>
        setError(err.response?.data?.message || 'Failed to load projects.'),
      )
  }

  useEffect(reload, [])

  const isAdmin = user.role === 'admin'

  return (
    <div className="page">
      <Topbar />
      <main className="container">
        <div className="page-header">
          <h1>Projects</h1>
          {isAdmin && !creating && (
            <button type="button" onClick={() => setCreating(true)}>
              + New project
            </button>
          )}
        </div>

        {creating && (
          <CreateProjectForm
            onCancel={() => setCreating(false)}
            onCreated={() => {
              setCreating(false)
              reload()
            }}
          />
        )}

        {error && <p className="error">{error}</p>}
        {projects === null && !error && <p className="muted">Loading…</p>}
        {projects?.length === 0 && (
          <p className="muted">No projects yet.</p>
        )}

        <div className="project-grid">
          {projects?.map((p) => (
            <ProjectCard key={p.id} project={p} showClient={isAdmin} />
          ))}
        </div>
      </main>
    </div>
  )
}

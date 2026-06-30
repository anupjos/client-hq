import { useEffect, useMemo, useState } from 'react'
import ActivityFeed from '../components/ActivityFeed'
import CreateProjectForm from '../components/CreateProjectForm'
import ProjectAvatar from '../components/ProjectAvatar'
import StatsTile from '../components/StatsTile'
import StatusBadge from '../components/StatusBadge'
import Topbar from '../components/Topbar'
import { Link } from 'react-router-dom'
import { listProjects } from '../api/projects'
import { listActivity } from '../api/activity'
import { formatRelativeTime, greeting } from '../utils/time'
import { useAuth } from '../hooks/useAuth'

const TILES = [
  { key: 'all', label: 'Total', accent: '#7C3AED' },
  { key: 'active', label: 'Active', accent: '#7C3AED' },
  { key: 'paused', label: 'Paused', accent: '#D97706' },
  { key: 'completed', label: 'Completed', accent: '#059669' },
]

export default function AdminDashboard() {
  const { user } = useAuth()
  const [projects, setProjects] = useState(null)
  const [activity, setActivity] = useState([])
  const [filter, setFilter] = useState('all')
  const [creating, setCreating] = useState(false)
  const [error, setError] = useState(null)

  const reload = () => {
    Promise.all([listProjects(), listActivity()])
      .then(([p, a]) => {
        setProjects(p)
        setActivity(a)
      })
      .catch((err) =>
        setError(err.response?.data?.message || 'Failed to load dashboard.'),
      )
  }

  useEffect(reload, [])

  const counts = useMemo(() => {
    if (!projects) return { all: 0, active: 0, paused: 0, completed: 0 }
    return {
      all: projects.length,
      active: projects.filter((p) => p.status === 'active').length,
      paused: projects.filter((p) => p.status === 'paused').length,
      completed: projects.filter((p) => p.status === 'completed').length,
    }
  }, [projects])

  const visible = useMemo(() => {
    if (!projects) return []
    if (filter === 'all') return projects
    return projects.filter((p) => p.status === filter)
  }, [projects, filter])

  return (
    <div className="page">
      <Topbar />
      <main className="container">
        <div className="dashboard-header">
          <div>
            <p className="muted small">{greeting()}, {user.name.split(' ')[0]}</p>
            <h1>Your portfolio</h1>
          </div>
          {!creating && (
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

        <div className="stats-row">
          {TILES.map((t) => (
            <StatsTile
              key={t.key}
              label={t.label}
              count={counts[t.key]}
              accent={t.accent}
              active={filter === t.key}
              onClick={() => setFilter(t.key)}
            />
          ))}
        </div>

        <div className="dashboard-layout">
          <section className="dashboard-main">
            {projects === null && <p className="muted">Loading…</p>}
            {projects?.length === 0 && (
              <p className="muted">No projects yet. Click "+ New project" to create one.</p>
            )}
            {visible.length === 0 && projects?.length > 0 && (
              <p className="muted">No projects match this filter.</p>
            )}
            <div className="project-grid-dense">
              {visible.map((p) => (
                <Link key={p.id} to={`/projects/${p.id}`} className="project-card-dense">
                  <ProjectAvatar name={p.name} size={36} />
                  <div className="project-card-dense-body">
                    <div className="project-card-dense-title">{p.name}</div>
                    <div className="project-card-dense-meta">
                      <StatusBadge status={p.status} />
                      {p.client && <span className="muted small">{p.client.name}</span>}
                    </div>
                    <div className="muted small">
                      {p.files_count ?? 0} file{p.files_count === 1 ? '' : 's'} ·
                      {' '}updated {formatRelativeTime(p.updated_at)}
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          </section>

          <aside className="dashboard-aside">
            <h3 className="panel-label">Recent activity</h3>
            <ActivityFeed items={activity} />
          </aside>
        </div>
      </main>
    </div>
  )
}

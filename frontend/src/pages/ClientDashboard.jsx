import { useEffect, useMemo, useState } from 'react'
import CompactProjectRow from '../components/CompactProjectRow'
import FeaturedProjectCard from '../components/FeaturedProjectCard'
import Topbar from '../components/Topbar'
import { listProjects } from '../api/projects'
import { listActivity } from '../api/activity'
import { greeting } from '../utils/time'
import { useAuth } from '../hooks/useAuth'

export default function ClientDashboard() {
  const { user } = useAuth()
  const [projects, setProjects] = useState(null)
  const [activity, setActivity] = useState([])
  const [error, setError] = useState(null)

  useEffect(() => {
    Promise.all([listProjects(), listActivity()])
      .then(([p, a]) => {
        setProjects(p)
        setActivity(a)
      })
      .catch((err) =>
        setError(err.response?.data?.message || 'Failed to load dashboard.'),
      )
  }, [])

  const featured = useMemo(() => {
    if (!projects || projects.length === 0) return null
    return (
      projects.find((p) => p.status === 'active') ||
      projects[0]
    )
  }, [projects])

  const others = useMemo(() => {
    if (!projects || !featured) return []
    return projects.filter((p) => p.id !== featured.id)
  }, [projects, featured])

  const featuredLastEvent = useMemo(() => {
    if (!featured) return null
    return activity.find((a) => a.project?.id === featured.id) || null
  }, [activity, featured])

  const activeCount = projects?.filter((p) => p.status === 'active').length ?? 0
  const archivedCount = projects?.filter((p) => p.status !== 'active').length ?? 0
  const firstName = user.name.split(' ')[0]

  return (
    <div className="page">
      <Topbar />
      <main className="container">
        <div className="lobby-hero">
          <h1>{greeting()}, {firstName}</h1>
          <p className="muted">
            {projects === null
              ? 'Loading your projects…'
              : projects.length === 0
                ? 'No projects assigned to you yet.'
                : `You have ${activeCount} active ${pluralize('project', activeCount)}` +
                  (archivedCount > 0
                    ? ` and ${archivedCount} ${pluralize('archived one', archivedCount, 'archived ones')}.`
                    : '.')}
          </p>
        </div>

        {error && <p className="error">{error}</p>}

        {featured && (
          <FeaturedProjectCard project={featured} lastEvent={featuredLastEvent} />
        )}

        {others.length > 0 && (
          <section className="lobby-others">
            <h3 className="panel-label">Other projects</h3>
            <div className="compact-list">
              {others.map((p) => (
                <CompactProjectRow key={p.id} project={p} />
              ))}
            </div>
          </section>
        )}
      </main>
    </div>
  )
}

function pluralize(word, n, plural) {
  if (n === 1) return word
  return plural || `${word}s`
}

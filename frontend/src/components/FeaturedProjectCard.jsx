import { Link } from 'react-router-dom'
import ProjectAvatar from './ProjectAvatar'
import StatusBadge from './StatusBadge'
import { formatRelativeTime } from '../utils/time'

export default function FeaturedProjectCard({ project, lastEvent }) {
  return (
    <Link to={`/projects/${project.id}`} className="featured-card">
      <div className="featured-card-top">
        <ProjectAvatar name={project.name} size={56} />
        <div className="featured-card-title">
          <h2>{project.name}</h2>
          <div className="featured-card-meta">
            <StatusBadge status={project.status} />
            <span className="muted small">
              {project.files_count ?? 0} file{project.files_count === 1 ? '' : 's'}
            </span>
          </div>
        </div>
        <div className="featured-card-cta">Open →</div>
      </div>

      {lastEvent && (
        <div className="featured-last-event">
          <span className="muted small">Latest activity</span>
          <p>
            <strong>{lastEvent.actor}</strong> {lastEvent.summary}
          </p>
          <span className="muted small">{formatRelativeTime(lastEvent.at)}</span>
        </div>
      )}
    </Link>
  )
}

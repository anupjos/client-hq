import { Link } from 'react-router-dom'
import StatusBadge from './StatusBadge'

export default function ProjectCard({ project, showClient }) {
  return (
    <Link to={`/projects/${project.id}`} className="project-card">
      <div className="project-card-header">
        <h2>{project.name}</h2>
        <StatusBadge status={project.status} />
      </div>
      {showClient && project.client && (
        <p className="muted small">Client: {project.client.name}</p>
      )}
      <p className="muted small">
        {project.files_count ?? 0} file{project.files_count === 1 ? '' : 's'}
      </p>
    </Link>
  )
}

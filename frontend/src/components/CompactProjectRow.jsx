import { Link } from 'react-router-dom'
import ProjectAvatar from './ProjectAvatar'
import StatusBadge from './StatusBadge'

export default function CompactProjectRow({ project }) {
  return (
    <Link to={`/projects/${project.id}`} className="compact-row">
      <ProjectAvatar name={project.name} size={28} />
      <span className="compact-row-name">{project.name}</span>
      <span className="muted small">
        {project.files_count ?? 0} file{project.files_count === 1 ? '' : 's'}
      </span>
      <StatusBadge status={project.status} />
    </Link>
  )
}

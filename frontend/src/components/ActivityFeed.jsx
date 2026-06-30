import { Link } from 'react-router-dom'
import { formatRelativeTime } from '../utils/time'

function ActivityIcon({ type }) {
  const map = {
    project_created: '+',
    file_uploaded: '↑',
    message_sent: '?',
    assistant_replied: '✺',
  }
  return <span className={`activity-icon activity-icon-${type}`}>{map[type] || '•'}</span>
}

export default function ActivityFeed({ items }) {
  if (!items || items.length === 0) {
    return <p className="muted small">No recent activity yet.</p>
  }
  return (
    <ul className="activity-feed">
      {items.map((item, i) => (
        <li key={i} className="activity-item">
          <ActivityIcon type={item.type} />
          <div className="activity-body">
            <div className="activity-line">
              <strong>{item.actor}</strong> {item.summary}
            </div>
            {item.project?.id && (
              <Link to={`/projects/${item.project.id}`} className="activity-project">
                {item.project.name}
              </Link>
            )}
            <div className="activity-time">{formatRelativeTime(item.at)}</div>
          </div>
        </li>
      ))}
    </ul>
  )
}

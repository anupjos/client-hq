import { getInitials, getProjectColors } from '../utils/avatar'

export default function ProjectAvatar({ name, size = 32 }) {
  const [from, to] = getProjectColors(name)
  return (
    <div
      className="project-avatar"
      style={{
        width: size,
        height: size,
        background: `linear-gradient(135deg, ${from}, ${to})`,
        fontSize: Math.round(size * 0.38),
        boxShadow: `0 2px 8px ${from}40`,
      }}
      aria-hidden="true"
    >
      {getInitials(name)}
    </div>
  )
}

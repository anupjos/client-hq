export default function StatsTile({ label, count, accent, active, onClick }) {
  const isClickable = typeof onClick === 'function'
  const Tag = isClickable ? 'button' : 'div'
  return (
    <Tag
      type={isClickable ? 'button' : undefined}
      className={`stats-tile${active ? ' is-active' : ''}${isClickable ? ' clickable' : ''}`}
      onClick={onClick}
      style={accent ? { '--tile-accent': accent } : undefined}
    >
      <span className="stats-tile-count">{count}</span>
      <span className="stats-tile-label">{label}</span>
    </Tag>
  )
}

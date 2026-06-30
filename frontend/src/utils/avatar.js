export function getInitials(name) {
  if (!name) return '?'
  const parts = name.trim().split(/\s+/).filter(Boolean)
  if (parts.length === 0) return '?'
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return (parts[0][0] + parts[1][0]).toUpperCase()
}

const PALETTE = [
  ['#7C3AED', '#EC4899'],
  ['#3B82F6', '#06B6D4'],
  ['#10B981', '#84CC16'],
  ['#F59E0B', '#EF4444'],
  ['#8B5CF6', '#3B82F6'],
  ['#EC4899', '#F59E0B'],
  ['#06B6D4', '#10B981'],
]

export function getProjectColors(name) {
  const seed = (name || '').split('').reduce((a, c) => a + c.charCodeAt(0), 0)
  return PALETTE[seed % PALETTE.length]
}

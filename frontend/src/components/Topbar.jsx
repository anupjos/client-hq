import { Link } from 'react-router-dom'
import { useAuth } from '../hooks/useAuth'

export default function Topbar() {
  const { user, logout } = useAuth()

  return (
    <header className="topbar">
      <Link to="/" className="brand"><strong>ClientHQ</strong></Link>
      <span className="muted">
        {user.name} · {user.role}
      </span>
      <button type="button" className="ghost" onClick={logout}>
        Log out
      </button>
    </header>
  )
}

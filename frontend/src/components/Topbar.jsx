import { Link } from 'react-router-dom'
import { useAuth } from '../hooks/useAuth'
import BrandMark from './BrandMark'

function LogoutIcon() {
  return (
    <svg
      width="14"
      height="14"
      viewBox="0 0 16 16"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.6"
      strokeLinecap="round"
      strokeLinejoin="round"
      aria-hidden="true"
    >
      <path d="M9.5 2.5H3.5a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h6" />
      <path d="M6.5 8h7.5" />
      <path d="M11.5 5.5 14 8l-2.5 2.5" />
    </svg>
  )
}

export default function Topbar() {
  const { user, logout } = useAuth()

  return (
    <header className="topbar">
      <Link to="/" className="brand">
        <BrandMark size={28} />
        <strong>ClientHQ</strong>
      </Link>
      <span className="muted">{user.name}</span>
      <button type="button" className="ghost with-icon" onClick={logout}>
        <LogoutIcon />
        Log out
      </button>
    </header>
  )
}

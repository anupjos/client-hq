import AdminDashboard from './AdminDashboard'
import ClientDashboard from './ClientDashboard'
import { useAuth } from '../hooks/useAuth'

export default function DashboardPage() {
  const { user } = useAuth()
  return user.role === 'admin' ? <AdminDashboard /> : <ClientDashboard />
}

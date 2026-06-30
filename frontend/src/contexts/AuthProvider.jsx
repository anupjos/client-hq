import { useCallback, useEffect, useState } from 'react'
import api, { tokenStore, setOnUnauthorized } from '../api/client'
import { AuthContext } from './AuthContext'

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  const clearSession = useCallback(() => {
    tokenStore.clear()
    setUser(null)
  }, [])

  useEffect(() => {
    setOnUnauthorized(() => clearSession())
  }, [clearSession])

  useEffect(() => {
    const token = tokenStore.get()
    if (!token) {
      setLoading(false)
      return
    }
    api
      .get('/me')
      .then(({ data }) => setUser(data))
      .catch(() => tokenStore.clear())
      .finally(() => setLoading(false))
  }, [])

  const login = async (email, password) => {
    const { data } = await api.post('/login', { email, password })
    tokenStore.set(data.token)
    setUser(data.user)
    return data.user
  }

  const logout = async () => {
    try {
      await api.post('/logout')
    } catch {
      // even on network error, drop local session
    }
    clearSession()
  }

  return (
    <AuthContext.Provider value={{ user, loading, login, logout }}>
      {children}
    </AuthContext.Provider>
  )
}

import { useEffect, useRef, useState } from 'react'
import { listMessages, sendMessage } from '../api/messages'

const POLL_INTERVAL_MS = 2000

export default function ChatPanel({ projectId }) {
  const [messages, setMessages] = useState([])
  const [draft, setDraft] = useState('')
  const [error, setError] = useState(null)
  const [sending, setSending] = useState(false)
  const pollTimer = useRef(null)
  const scrollRef = useRef(null)

  const stopPolling = () => {
    if (pollTimer.current) {
      clearInterval(pollTimer.current)
      pollTimer.current = null
    }
  }

  const startPolling = () => {
    if (pollTimer.current) return
    pollTimer.current = setInterval(refresh, POLL_INTERVAL_MS)
  }

  const refresh = async () => {
    try {
      const data = await listMessages(projectId)
      setMessages(data)
      const stillPending = data.some((m) => m.status === 'pending')
      if (!stillPending) stopPolling()
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to load messages.')
      stopPolling()
    }
  }

  useEffect(() => {
    refresh().then(() => {
      if (messages.some((m) => m.status === 'pending')) startPolling()
    })
    return stopPolling
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [projectId])

  useEffect(() => {
    if (scrollRef.current) {
      scrollRef.current.scrollTop = scrollRef.current.scrollHeight
    }
  }, [messages])

  const handleSubmit = async (e) => {
    e.preventDefault()
    if (!draft.trim() || sending) return
    setError(null)
    setSending(true)
    try {
      await sendMessage(projectId, draft.trim())
      setDraft('')
      await refresh()
      startPolling()
    } catch (err) {
      const data = err.response?.data
      setError(
        data?.errors
          ? Object.values(data.errors).flat().join(' ')
          : data?.message || 'Send failed.',
      )
    } finally {
      setSending(false)
    }
  }

  return (
    <div className="chat">
      <div className="chat-messages" ref={scrollRef}>
        {messages.length === 0 && (
          <p className="muted small">Ask anything about this project.</p>
        )}
        {messages.map((m) => (
          <div key={m.id} className={`bubble bubble-${m.role}`}>
            {m.status === 'pending' ? (
              <em className="muted">Thinking…</em>
            ) : m.status === 'failed' ? (
              <span className="error">{m.error || 'Assistant failed.'}</span>
            ) : (
              <span className="bubble-text">{m.content}</span>
            )}
          </div>
        ))}
      </div>

      {error && <p className="error">{error}</p>}

      <form className="chat-composer" onSubmit={handleSubmit}>
        <textarea
          rows={2}
          placeholder="Ask the AI about this project…"
          value={draft}
          onChange={(e) => setDraft(e.target.value)}
          onKeyDown={(e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
              e.preventDefault()
              handleSubmit(e)
            }
          }}
          disabled={sending}
        />
        <button type="submit" disabled={sending || !draft.trim()}>
          {sending ? 'Sending…' : 'Send'}
        </button>
      </form>
    </div>
  )
}

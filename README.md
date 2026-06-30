# ClientHQ

An AI-powered client portal. Admins create projects and assign them to clients;
each client gets a dashboard with their project status, files, and a
project-aware AI chat that answers questions using the project's notes and
uploaded files as context.

## Stack

| Layer | Tech |
|---|---|
| Backend | PHP 8.3, Laravel 13, Laravel Sanctum (token auth) |
| Database | Postgres 16 (via Docker) |
| Queue | Laravel database queue + `php artisan queue:work` |
| Storage | Laravel filesystem (`local` disk) |
| AI | Anthropic Messages API (Claude) |
| Frontend | Vite, React 19 (plain JS), Axios, React Router |

## Architecture notes

- **Auth.** Bearer-token Sanctum (`HasApiTokens` on `User`). Frontend stores
  the token in `localStorage` and sends it on every request via an Axios
  interceptor; a 401 clears the token and redirects to login.
- **Authorization.** `ProjectPolicy` enforces who can view/update each project.
  Clients only see their own projects; admins see all.
- **AI context (RAG-lite).** When a user sends a chat message, the backend
  creates a pending placeholder for the assistant reply and dispatches
  `ProcessAiChatJob`. The job builds a system prompt from the project's notes
  plus the contents of any uploaded `.txt`/`.md` files, calls the Anthropic
  Messages API, and updates the placeholder with the reply. The frontend
  polls every 2s while a message is pending.
- **Enums.** `UserRole`, `ProjectStatus`, `ChatMessageRole`, and
  `ChatMessageStatus` are PHP 8.1+ backed enums cast on their Eloquent models.
- **Validation.** Each write endpoint has a dedicated `FormRequest` class.

## API

All endpoints below require `Authorization: Bearer <token>` except `/api/login`.

| Method | Path | Notes |
|---|---|---|
| POST | `/api/login` | returns `{ token, user }` |
| GET | `/api/me` | current user |
| POST | `/api/logout` | revokes current token |
| GET | `/api/clients` | admin only |
| GET | `/api/projects` | admin: all; client: only their own |
| POST | `/api/projects` | admin only |
| GET | `/api/projects/{id}` | includes files |
| PATCH | `/api/projects/{id}` | admin only |
| DELETE | `/api/projects/{id}` | admin only |
| GET | `/api/projects/{id}/files` |  |
| POST | `/api/projects/{id}/files` | multipart, field name `file` |
| GET | `/api/projects/{id}/files/{file}` | binary download |
| DELETE | `/api/projects/{id}/files/{file}` | admin only |
| GET | `/api/projects/{id}/messages` |  |
| POST | `/api/projects/{id}/messages` | queues AI job, returns user message + pending placeholder |

## Local setup

Prerequisites: PHP 8.3, Composer, Node 20+, Docker.

```bash
# 1. Postgres
docker compose up -d   # Postgres on :5434

# 2. Backend
cd backend
cp .env.example .env
composer install
php artisan key:generate
# Set ANTHROPIC_API_KEY in .env (required for the AI chat)
php artisan migrate --seed

# 3. Frontend
cd ../frontend
cp .env.example .env
npm install
```

Run all three in separate terminals:

```bash
# Terminal 1 — API on :8000
cd backend && php artisan serve

# Terminal 2 — queue worker (processes AI chat jobs)
cd backend && php artisan queue:work

# Terminal 3 — Vite dev server on :5173
cd frontend && npm run dev
```

Open http://localhost:5173.

## Demo accounts

The seeder creates:

| Role | Email | Password |
|---|---|---|
| Admin | admin@demo.test | password |
| Client | client@demo.test | password |

Plus one demo project (`Acme Co. Website Redesign`) with two text files and a
small message history so the chat panel has something to render before you
ask anything.

## Tests

```bash
cd backend
php artisan test
```

Covers auth, projects, files, chat messages, and the AI job (with the
Anthropic HTTP call faked via `Http::fake`).

## Code style

Backend runs Laravel Pint:

```bash
cd backend && ./vendor/bin/pint
```

Frontend runs oxlint:

```bash
cd frontend && npm run lint
```

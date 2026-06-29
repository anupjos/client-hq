# ClientHQ

An AI-powered client portal. Admins create projects and assign them to clients; each
client gets a dashboard with their project status, files, and a project-aware AI chat
that answers questions using the project's notes and uploaded files as context.

**Status:** Work in progress.

## Stack
- **Backend:** PHP 8.3 + Laravel 13, Laravel Sanctum (token auth), Postgres 16, queue jobs for AI requests
- **Frontend:** Vite + React (plain JS), Axios
- **AI:** Claude (Anthropic Messages API)
- **Infra:** Docker Compose for Postgres; backend and frontend run natively

## Quick start
1. `docker compose up -d` — starts Postgres on `:5434`
2. `cd backend && cp .env.example .env && composer install && php artisan key:generate`
3. Add your `ANTHROPIC_API_KEY` to `backend/.env`
4. `php artisan migrate --seed`
5. `php artisan serve` (API on `:8000`) and `php artisan queue:work` in another terminal
6. `cd frontend && npm install && npm run dev` (UI on `:5173`)

Demo accounts will be created by the seeder.

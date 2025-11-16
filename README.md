## Telegram Bot Hosting Platform

This repository contains a production-oriented platform for hosting multiple Telegram bots and related services on a single server.

- Backend: Laravel microservices (API gateway, monitoring, bot manager).
- Frontend: Vue 3 + TypeScript SPA based on the Xintra admin template.
- Reverse proxy: `nginx-proxy` with automatic SSL via `letsencrypt-nginx-proxy-companion`.
- Deployment: Docker and `docker-compose`.

For architecture details, see `ARCHITECTURE.md`.


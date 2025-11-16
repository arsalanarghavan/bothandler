## Overview

This repository hosts a **Telegram Bot Hosting Platform** built as a set of Laravel microservices with a Vue 3 SPA dashboard, all running inside Docker and exposed behind an Nginx reverse proxy.

- **Backend**: Laravel (PHP 8+) microservices, API-first only.
- **Frontend**: Vue 3 + TypeScript + Vite SPA.
- **Reverse proxy**: `nginx-proxy` + `letsencrypt-nginx-proxy-companion`.
- **Container orchestration**: `docker-compose` (single-server deployment).

The goal is to host multiple independent Telegram bots and services on a single server, manage them through a unified dashboard, and route each domain to its own Docker container automatically.

## Repository Structure

- `backend/`
  - `api-gateway/` – Public-facing Laravel API gateway:
    - Authentication and user management.
    - RBAC and permissions.
    - Aggregated dashboard APIs (calls monitoring and bot-manager).
  - `monitoring-service/` – Internal Laravel service:
    - Reads Docker metrics via `/var/run/docker.sock`.
    - Exposes APIs for containers, services, and bot resource usage.
  - `bot-manager/` – Internal Laravel service:
    - Stores bot metadata, GitHub repos, deployment history.
    - Orchestrates Docker builds/runs for bot services.
  - `shared/` – Shared PHP code (helpers, DTOs, middleware) to be reused by services (e.g. via a local Composer package).
- `frontend/` – Vue 3 SPA dashboard (Vite, TypeScript):
  - `src/layouts/` – Application-level layouts (main dashboard layout).
  - `src/components/` – Reusable UI components (layout, dashboard widgets, forms).
  - `src/views/` – Page-level views (dashboard, bots, services, settings).
  - `src/router/` – Client-side routes.
  - `src/store/` – Pinia stores.
  - `src/services/api/` – Axios-based API clients.
  - `src/i18n/` – vue-i18n configuration and language files.
- `common/types/` – Shared TypeScript interfaces generated from OpenAPI specs.
- `docker/` – Docker-related configuration that is not tied to one service (e.g. base images, helper scripts).
- `scripts/` – Utility scripts for local dev and CI/CD.
- `docs/` – Additional documentation and API specs (e.g. OpenAPI YAML files).
- `bots/` – Example bot services for local development.
- `.github/workflows/` – CI/CD pipelines.

## Microservice Responsibilities

### API Gateway (`backend/api-gateway`)

- Exposes the public HTTP API consumed by the Vue SPA (e.g. `/api/**`).
- Handles:
  - User registration/login/logout.
  - JWT issuance/refresh and basic RBAC.
  - Aggregated dashboard endpoints such as:
    - `/api/dashboard/overview`
    - `/api/bots`
    - `/api/services`
- Communicates with other backend services via internal HTTP calls over the Docker network.

### Monitoring Service (`backend/monitoring-service`)

- Reads Docker state and metrics via the Docker Engine API (mounted `docker.sock`).
- Provides APIs such as:
  - `GET /api/containers`
  - `GET /api/containers/{id}`
  - `GET /api/summary`
  - `GET /api/bots/{botId}/metrics`
- Responsible for mapping containers to bots/services using Docker labels:
  - `bot-id`
  - `service-type`
  - `virtual-host` (domain).

### Bot Manager (`backend/bot-manager`)

- Stores and manages:
  - Bot definitions (name, description, token, domain).
  - Git repositories and branches/tags for each bot service.
  - Deployment and release history.
- Provides APIs:
  - `POST /api/bots` – register a new bot based on a GitHub repo.
  - `POST /api/bots/{id}/deploy` – trigger a deployment/update.
  - `GET /api/bots/{id}/deployments` – view deployment history.
- Orchestrates deployments by:
  - Cloning or pulling the GitHub repo into a local volume.
  - Building or pulling a Docker image for the bot.
  - Running containers with appropriate `VIRTUAL_HOST` / `LETSENCRYPT_HOST` labels.
  - Storing deployment status and logs for the dashboard.

### Shared Backend Code (`backend/shared`)

- Houses code reused across Laravel services:
  - Auth helpers and middleware.
  - Common exception types and response macros.
  - Shared DTOs for cross-service communication.
- Packaged as a local Composer dependency that each service requires.

## Frontend Architecture (Vue 3)

- SPA built with Vue 3 + TypeScript + Vite.
- Uses:
  - Vue Router for client-side routing.
  - Pinia for state management.
  - Axios for HTTP requests.
  - vue-i18n for multi-language support (at least `fa` and `en`).
- Layout/components derived from the Xintra HTML template:
  - `MainLayout` as the main app shell.
  - `AppHeader`, `AppSidebar`, `AppFooter` as separate components.
  - Dashboard widgets (cards, charts, tables) split into reusable components.

### Key Views

- `DashboardOverviewView` – global server and bot/service metrics.
- `BotsListView` and `BotDetailView` – manage bots and view metrics/deployments.
- `ServicesListView` – list all running containers/services.
- `DeployBotView` – form to register/deploy a bot via GitHub.
- `SettingsView` – configuration (Telegram tokens, language preference, etc.).

## API Design & Contracts

- All backend services are **API-first**:
  - No server-rendered HTML.
  - All responses are JSON with consistent envelope and error format.
- API routes live under `/api/**` only.
- OpenAPI/Swagger specs are stored under `docs/`:
  - `docs/api-gateway.yaml`
  - `docs/monitoring-service.yaml`
  - `docs/bot-manager.yaml`
- TypeScript types for the frontend are generated into `common/types/` from these specs.

## Security & Auth

- Authentication:
  - JWT-based auth (library choice per Laravel best practice).
  - Access tokens used by the SPA; refresh strategy via dedicated refresh endpoint.
- RBAC:
  - Roles like `admin`, `devops`, `viewer` enforced at the API gateway.
- Docker security:
  - Only internal services access Docker (via Docker network).
  - Docker socket mounted read-only where possible and restricted to monitoring/bot-manager.
  - GitHub repos validated and sanitized before being used in any shell or Docker commands.

## Deployment Topology

- Single-server deployment orchestrated via `docker-compose`:
  - `nginx-proxy` + `letsencrypt-nginx-proxy-companion`.
  - `api-gateway`, `monitoring-service`, `bot-manager`, `frontend`.
  - Bot containers, each with a dedicated `VIRTUAL_HOST`/`LETSENCRYPT_HOST`.
- Domains:
  - `dashboard.example.com` → frontend SPA.
  - `api.example.com` → API gateway.
  - `monitor.example.com` (optional, usually internal) → monitoring endpoints or only via gateway.
  - `*.bot.example.com` or custom domains → individual bot containers.

This document should be kept up to date when new microservices, deployment patterns, or cross-cutting concerns are introduced.



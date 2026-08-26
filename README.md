# Magic HTML Content Service

Tier 1 singleton-content capability for Magic HTML static sites. It owns site-scoped content drafts and immutable published snapshots. It does not expose collection, form, or media operations.

## Contract

- `GET /api` — capability document
- `GET /api/__verify` — runtime readiness
- `PUT /api/v1/sites/{site}/contents/{resource}` — create or replace a content draft
- `POST /api/v1/sites/{site}/snapshots` — publish an immutable content snapshot
- `GET /api/v1/sites/{site}/snapshots/{version}` — retrieve a snapshot
- `GET /api/v1/sites/{site}/published/contents/{resource}` — public runtime projection

Writes and snapshot reads require the service Bearer token. Published content reads are public and CORS-enabled. Every record is scoped by `site`.

Each draft owns its JSON Schema and is rejected when its value does not satisfy it. `media_refs` are stable references to the independent Media Service; this service never calls an object store.

```bash
composer install
php artisan migrate
php artisan test --compact
```

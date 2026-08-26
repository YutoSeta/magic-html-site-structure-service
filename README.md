# Magic HTML Site Structure Service

The canonical owner of a site's information architecture. It stores one versioned Site AST per site and can generate a new Site AST from a structured interview brief.

It does not own page HTML, content values, wireframes, media, design, or publishing.

## API

All `/api/v1` operations require `Authorization: Bearer <MAGIC_HTML_SERVICE_TOKEN>`.

- `GET /api` — capability document
- `GET /api/__verify` — contract, database, and generator readiness
- `GET /api/v1/sites/{site}/structure`
- `PUT /api/v1/sites/{site}/structure`
- `POST /api/v1/sites/{site}/structure/generate`
- `DELETE /api/v1/sites/{site}/structure`

The canonical `structure` contains `site`, ordered `pages`, and `navigation`. The first page is always `home` at `/`; page keys and paths are unique and independent from visual design.

## Verification

```bash
composer install
php artisan test --compact
composer validate --strict
composer audit --no-dev
```

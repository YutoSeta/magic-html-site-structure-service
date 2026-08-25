# Magic HTML CMS Service

Tier 1 content capability for Magic HTML StaticSite output. It owns site-scoped content, collections, media metadata and immutable published snapshots. Media bytes live in an S3-compatible object store such as Cloudflare R2; content stores stable `media_key` references instead of provider URLs.

## Contract

The service implements contract `1.0` from `yutoseta/magic-html-contracts`:

- `GET /api` — capability document
- `GET /api/__verify` — database and object-storage verification
- `PUT /api/v1/sites/{site}/{contents|collections|media}/{resource}` — create or replace a draft resource
- `POST /api/v1/sites/{site}/media` — upload a draft media object
- `GET /api/v1/sites/{site}/media/{media}/file` — authenticated draft preview
- `POST /api/v1/sites/{site}/snapshots` — publish an immutable snapshot
- `GET /api/v1/sites/{site}/snapshots/{version}` — retrieve an immutable snapshot
- `GET /media/{site}/{media}` — public media bytes, available only after publication

All `/api/v1` operations require the service bearer token. Public media URLs fail closed until a snapshot containing the media is published. Every resource is scoped by the site identifier, including media lookup and snapshot retrieval.

## Resource model

`contents` hold singleton content, while `collections` hold repeatable structured data. Each draft carries its own JSON Schema and is rejected if its value does not satisfy that schema. `media_refs` must resolve to media in the same site before publication.

```json
{
  "contract_version": "1.0",
  "name": "Homepage",
  "schema": {
    "type": "object",
    "required": ["title"],
    "properties": {"title": {"type": "string"}}
  },
  "value": {"title": "Magic HTML"},
  "media_refs": []
}
```

## Runtime

Production uses PostgreSQL and an S3-compatible private bucket. Set `MAGIC_HTML_SERVICE_TOKEN`, database variables, `CMS_MEDIA_DISK=s3`, and the usual `AWS_*` variables. For Cloudflare R2, set `AWS_DEFAULT_REGION=auto`, the R2 S3 endpoint in `AWS_ENDPOINT`, and `AWS_USE_PATH_STYLE_ENDPOINT=false`.

Run locally with:

```bash
composer install
php artisan migrate
php artisan test --compact
```

# PHP Announcements API — Learning Exercise

A small local exercise for learning PHP request handling, JSON and input validation, using synthetic school announcements. Built incrementally with AI-assisted explanations and code review while I implement and test the code.

This is a work in progress, not production-ready school software. It contains no real student or school data.

## Current behaviour

- `GET /hello.php` returns a hard-coded list of announcements as JSON.
- `POST /hello.php` reads JSON, validates the title and body, trims surrounding whitespace and returns those two fields. **It does not create or save an announcement.**
- Malformed JSON receives `400`.
- Missing, non-string or whitespace-only title/body values receive `422`.
- Unsupported HTTP methods receive `405` with `Allow: GET, POST`.

## Run locally

Requires PHP with its command-line interpreter. Development so far has used PHP 8.5.12. No framework, Composer packages or database are currently required.

From the repository folder:

```sh
php -v
php -S localhost:8000
```

Leave that terminal running. Open `http://localhost:8000/hello.php` in a browser, or use the requests below from a second terminal. Stop the server with Control+C.

PHP's built-in server is used only for local development.

## Example requests

List the synthetic announcements:

```sh
curl -i http://localhost:8000/hello.php
```

Validate and return a submitted announcement:

```sh
curl -i -X POST http://localhost:8000/hello.php \
  -H 'Content-Type: application/json' \
  -d '{"title":"Music club","body":"Meet after school.   "}'
```

Expected status: `200`. Response body:

```json
{"title":"Music club","body":"Meet after school."}
```

Reject a numeric title:

```sh
curl -i -X POST http://localhost:8000/hello.php \
  -H 'Content-Type: application/json' \
  -d '{"title":42,"body":"Meet after school."}'
```

Expected status: `422`. Response body:

```json
{"error":"Title must be a non-empty string"}
```

Reject malformed JSON:

```sh
curl -i -X POST http://localhost:8000/hello.php \
  -H 'Content-Type: application/json' \
  -d '{"title":"Music club"'
```

Expected status: `400`, with `{"error":"Invalid JSON"}`.

## Design decisions

- One request-handling file keeps the initial request/response flow visible while learning the language.
- A shared `isNonEmptyString()` function applies the same validation rule to both fields. It checks the type before calling `trim()`.
- JSON decoding converts request text into PHP data; JSON encoding converts response data back into text.
- The response explicitly selects title and body rather than echoing all submitted fields.
- Malformed JSON and invalid field values use separate status codes: `400` and `422`.

## Verification and limitations

Manual curl checks have covered GET, valid POST, malformed JSON, unsupported methods, numeric titles, blank bodies and whitespace trimming. Three automated validation checks cover a non-empty string, whitespace-only input and a numeric value. Run them with `php validation.test.php`.

There are currently no classes, database, persistence, update/delete operations, authentication or deployment. The API also lacks explicit top-level JSON object validation, request Content-Type enforcement, field length limits and request-size limits. These boundaries matter before considering broader use.

The next learning step is a small object-oriented PHP structure with SQLite/PDO for persisted create-and-list behaviour. These features are planned, not implemented; update/delete are optional if time permits.

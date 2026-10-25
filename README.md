# PHP Announcements API — Learning Exercise

A small local exercise for learning PHP request handling, JSON and input validation, using synthetic school announcements. Built incrementally with AI-assisted explanations and code review while I implement and test the code.

This is a work in progress, not production-ready school software. It contains no real student or school data.

## Current behaviour

- `GET /hello.php` returns a hard-coded list of announcements as JSON.
- `POST /hello.php` reads JSON, validates the title and body, trims surrounding whitespace and returns those two fields. **It does not create or save an announcement.**
- Malformed JSON receives `400`.
- Missing, non-string or whitespace-only title/body values receive `422`.
- Unsupported HTTP methods receive `405` with `Allow: GET, POST`.
- `php database.php` opens a local SQLite database, creates the announcements table if needed, and lists saved rows through `AnnouncementRepository::findAll()`. This script is separate from the HTTP API.
- The repository also provides `create()` to insert an announcement using named placeholders and separately supplied values. Automated repository tests exercise creation and retrieval; the HTTP API does not call the repository yet.

## Run locally

Requires PHP with its command-line interpreter and PDO SQLite support. Development so far has used PHP 8.5.12. No framework or Composer packages are required.

From the repository folder:

```sh
php -v
php -S localhost:8000
```

Leave that terminal running. Open `http://localhost:8000/hello.php` in a browser, or use the requests below from a second terminal. Stop the server with Control+C.

PHP's built-in server is used only for local development.

### SQLite practice script

From the repository folder, run:

```sh
php database.php
```

This creates `announcements.sqlite` and the announcements table if absent, then prints the saved rows as JSON. A fresh database returns `[]`; the script does not insert seed data. Local database files and their supporting files are excluded from Git through `.gitignore`. No web server is needed for this script.

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
- `AnnouncementValidator.php` defines a small class whose public `isNonEmptyString()` method applies the same validation rule to both fields. It checks the type before calling `trim()`. This stateless helper could also be a standalone function; the class introduces object creation and method calls as a learning step.
- The API and validation tests load the same class using `require_once` and create their own validator objects.
- `AnnouncementRepository` receives a PDO connection through its constructor and owns the SQL for inserting and retrieving announcements. Inserts use `prepare()` and `execute()` with named placeholders, keeping values separate from SQL instructions. The practice script handles connection setup, table creation and output.
- JSON decoding converts request text into PHP data; JSON encoding converts response data back into text.
- The response explicitly selects title and body rather than echoing all submitted fields.
- Malformed JSON and invalid field values use separate status codes: `400` and `422`.

## Verification and limitations

Manual curl checks have covered GET, valid POST, malformed JSON, unsupported methods, numeric titles, blank bodies and whitespace trimming. Three automated validation checks cover a non-empty string, whitespace-only input and a numeric value. Run them with `php validation.test.php`.

The SQLite practice script has been manually checked to retrieve a previously inserted synthetic row. Three automated repository checks cover an empty database, creating and retrieving an announcement, and preserving apostrophes in saved text. Each test-script run uses a fresh in-memory SQLite database, leaving the local database file untouched.

Run both test scripts from the repository folder:

```sh
php validation.test.php
php repository.test.php
```

Each script prints three PASS messages and exits with code `0` when its checks succeed; a failed check exits with code `1`.

SQLite creation and retrieval are implemented in the repository, with a standalone practice script for listing saved rows. The HTTP API is not yet connected to the database: GET remains hard-coded, and POST validates and returns data without saving it. There is no update/delete functionality, authentication or deployment. The API also lacks explicit top-level JSON object validation, request Content-Type enforcement, field length limits and request-size limits. These boundaries matter before considering broader use.

The next learning steps are connecting the API to SQLite and verifying the persisted create-and-list HTTP flow. These are planned, not implemented; update/delete are optional if time permits.

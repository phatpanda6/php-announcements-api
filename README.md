# PHP Announcements API — Learning Exercise

A small local exercise for learning PHP request handling, JSON and input validation, using synthetic school announcements. Built incrementally with AI-assisted explanations and code review while I implement and test the code.

This is a work in progress, not production-ready school software. It contains no real student or school data.

## Current behaviour

- `GET /hello.php` returns saved announcements from SQLite as JSON, including their IDs, ordered by ID. An empty database returns `[]`.
- `POST /hello.php` reads JSON, validates the title and body, trims surrounding whitespace, saves an announcement to SQLite and returns the cleaned title and body with `201 Created`. The POST response currently does not include the assigned ID.
- Malformed JSON receives `400`.
- Valid JSON that is not an object, such as `42`, `null` or `[]`, receives `422` with an object-shape error.
- Missing, non-string or whitespace-only title/body values receive `422`.
- Unsupported HTTP methods receive `405` with `Allow: GET, POST`.
- Unexpected exceptions and errors thrown during setup or request handling are caught and return `500` with a generic JSON error, without exposing the exception message in that response.
- `php database.php` opens a local SQLite database, creates the announcements table if needed, and lists saved rows through `AnnouncementRepository::findAll()`. This script is separate from the HTTP API.
- The API uses the repository's `create()` and `findAll()` methods for persistence. Inserts use named placeholders and separately supplied values.

## Run locally

Requires PHP with its command-line interpreter and PDO SQLite support. Developed locally with PHP 8.5.12; the six automated checks also passed on PHP 8.3.6 in the EC2 lab below. No framework or Composer packages are required.

From the repository folder:

```sh
php -v
php -S localhost:8000
```

Leave that terminal running. Open `http://localhost:8000/hello.php` in a browser, or use the requests below from a second terminal. Stop the server with Control+C.

PHP's built-in server is used only for local development.

The API automatically creates the local `announcements.sqlite` database and announcements table on first use. It does not seed announcements. Each successful POST adds a row; rows remain across requests and server restarts. Database files and their supporting files are excluded from Git through `.gitignore`.

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

Create a saved announcement:

```sh
curl -i -X POST http://localhost:8000/hello.php \
  -H 'Content-Type: application/json' \
  -d '{"title":"Music club","body":"Meet after school.   "}'
```

Expected status: `201 Created`. Response body:

```json
{"title":"Music club","body":"Meet after school."}
```

Run the GET request again to retrieve the saved announcement with its assigned `id`. Repeating the POST creates another row.

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

Reject valid JSON with the wrong shape:

```sh
curl -i -X POST http://localhost:8000/hello.php \
  -H 'Content-Type: application/json' \
  -d '42'
```

Expected status: `422`, with `{"error":"Request body must be a JSON object"}`.

## Design decisions

- One request-handling file keeps the initial request/response flow visible while learning the language.
- `AnnouncementValidator.php` defines a small class whose public `isNonEmptyString()` method applies the same validation rule to both fields. It checks the type before calling `trim()`. This stateless helper could also be a standalone function; the class introduces object creation and method calls as a learning step.
- The API and validation tests load the same class using `require_once` and create their own validator objects.
- `AnnouncementRepository` receives a PDO connection through its constructor and owns the SQL for inserting and retrieving announcements. Inserts use `prepare()` and `execute()` with named placeholders, keeping values separate from SQL instructions.
- `database-connection.php` provides shared connection and table setup without output. Both the API and the listing practice script load it; each caller controls its own response or output.
- JSON decoding converts request text into PHP data; JSON encoding converts response data back into text.
- Decoding preserves JSON objects as PHP objects so the API can distinguish an announcement object from a JSON array or scalar before validating its fields.
- The response explicitly selects title and body rather than echoing all submitted fields.
- Malformed JSON and invalid field values use separate status codes: `400` and `422`.
- A top-level `try`/`catch (Throwable $error)` wraps setup and request handling. Expected validation failures retain their specific responses; thrown unexpected failures receive a generic `500` JSON response.

## Verification and limitations

Manual curl checks have covered GET, valid POST, malformed JSON, unsupported methods, numeric titles, blank bodies and whitespace trimming. Three automated validation checks cover a non-empty string, whitespace-only input and a numeric value. Run them with `php validation.test.php`.

The SQLite practice script has been manually checked to retrieve a previously inserted synthetic row. Three automated repository checks cover an empty database, creating and retrieving an announcement, and preserving apostrophes in saved text. Each test-script run uses a fresh in-memory SQLite database, leaving the local database file untouched.

Run both test scripts from the repository folder:

```sh
php validation.test.php
php repository.test.php
```

Each script prints three PASS messages and exits with code `0` when its checks succeed; a failed check exits with code `1`.

The persisted POST-then-GET flow has been manually checked, and a read-only database check confirmed the synthetic announcement was stored with trimmed values. Automated tests cover the validator and repository, not the complete HTTP flow.

The object-shape rejection was manually checked with a JSON number (`42`). The unexpected-error path was manually checked by temporarily throwing an exception before setup: the response was `500` with only `{"error":"Internal server error"}`. The temporary exception was removed, normal GET returned `200` again, and all six automated checks passed afterward. These HTTP error-path checks are manual, not part of the automated scripts.

There is no update/delete functionality, authentication or production deployment. The API also lacks request Content-Type enforcement, field length limits, request-size limits and server-side logging of caught exceptions. The error handler does not convert PHP warnings into exceptions or undo output already written before an error. These boundaries matter before considering broader use.

The core learning scope is persisted create-and-list behaviour with input validation and basic unexpected-error handling. Update/delete remain optional future work.

## Introductory AWS EC2 deployment lab

Completed guided runtime exercises on 26–28 October 2026 to learn basic EC2, Linux, SSH and deployment troubleshooting. This was a temporary learning deployment using synthetic data, not production hosting or commercial AWS experience. Cleanup verification is still pending; the lab is not yet recorded as fully closed out.

### Architecture and access

```text
Mac curl → Mac 127.0.0.1:8001 → encrypted SSH tunnel
         → EC2 127.0.0.1:8000 → PHP API → SQLite on the EC2 disk
```

- One Ubuntu Server 24.04 LTS x86 instance (`t3.micro`) in Sydney (`ap-southeast-2`).
- One encrypted 8 GiB gp3 root volume, configured to delete on termination; Standard CPU credit mode.
- SSH key authentication, with inbound TCP port 22 restricted to the laptop's public IPv4 address (`/32`). No inbound HTTP, HTTPS or port 8000 rules.
- PHP listened on EC2's loopback address only. The tunnel also listened on the Mac's loopback address only. The development server, project files and SQLite file were not exposed through a public web listener.
- Account credits and regional prices were checked before launch. A US$2 monthly cost budget email alert was configured, excluding credit/refund offsets. Budget alerts are delayed notifications, not spending caps or automatic shutdowns.

### Setup used

Replace `EC2_PUBLIC_IP` and `KEY_PATH` below with the current instance address and the local private-key path. Keep the private key outside the repository; never commit or share its contents. The instance's public IP can change after a stop/start, and the SSH source rule must match the laptop's current public IP.

On the **Mac**, restrict private-key permissions and connect:

```sh
chmod 400 KEY_PATH
ssh -o ServerAliveInterval=30 -i KEY_PATH ubuntu@EC2_PUBLIC_IP
```

Before accepting the first SSH trust prompt, compare its ED25519 fingerprint with the instance's SSH host-key fingerprint in the AWS console system log.

In the **EC2 SSH session**, install PHP CLI and SQLite support from Ubuntu's package repositories and create the project folder:

```sh
sudo apt update
sudo apt install php-cli php-sqlite3
php -v
php -r 'echo implode(", ", PDO::getAvailableDrivers()), PHP_EOL;'
mkdir -p ~/php-announcements-api
```

In a separate **Mac terminal**, from the local repository folder, transfer only PHP files:

```sh
scp -i KEY_PATH ./*.php ubuntu@EC2_PUBLIC_IP:~/php-announcements-api/
```

This excludes the local database, Git history and private key. In the **EC2 session**, run the existing tests and start PHP:

```sh
cd ~/php-announcements-api
php validation.test.php
php repository.test.php
php -S 127.0.0.1:8000
```

Leave that session running. In a second **Mac terminal**, open the tunnel:

```sh
ssh -i KEY_PATH \
  -o ExitOnForwardFailure=yes \
  -o ServerAliveInterval=30 \
  -N -L 127.0.0.1:8001:127.0.0.1:8000 \
  ubuntu@EC2_PUBLIC_IP
```

The tunnel stays in the foreground without a prompt. Send requests from a third **Mac terminal**.

### Verification and sanitised evidence

Observed on EC2: PHP 8.3.6, PDO driver `sqlite`, and all three validation plus all three repository checks reporting PASS. The HTTP checks below were manual, not automated end-to-end tests.

```sh
curl -i http://127.0.0.1:8001/hello.php
# Initially: 200 OK, []

curl -i -X POST http://127.0.0.1:8001/hello.php \
  -H 'Content-Type: application/json' \
  -d '{"title":"  EC2 lab check  ","body":"  Saved through the SSH tunnel.  "}'
# 201 Created
# {"title":"EC2 lab check","body":"Saved through the SSH tunnel."}

curl -i http://127.0.0.1:8001/hello.php
# 200 OK
# [{"id":1,"title":"EC2 lab check","body":"Saved through the SSH tunnel."}]

curl -i -X DELETE http://127.0.0.1:8001/hello.php
# 405 Method Not Allowed
# Allow: GET, POST
# {"error":"Method not allowed"}
```

After stopping and starting the same EC2 instance, reconnecting to its new public IP, and restarting PHP and the tunnel, GET returned the same saved row. This demonstrated persistence on the retained EBS disk beyond the lifetime of the PHP process and across the instance stop/start.

For a controlled failure, an unsupported DELETE request was deliberately sent. The client received 405, and the EC2 PHP terminal recorded the matching `[405]: DELETE /hello.php` request. The existing method restriction explained the response; no data deletion or code change was needed. This exercised request-log inspection, not diagnosis of an unexpected infrastructure outage.

### Cleanup instructions and status

**Pending verification:** termination and the post-termination resource inventory have not yet been confirmed in the recorded evidence.

1. In the Sydney EC2 console, select the lab instance and choose **Instance state → Terminate (delete) instance**. This permanently removes the server and its synthetic data; verify its state becomes **Terminated**.
2. Under **Volumes**, confirm the lab root volume was deleted. Check **Snapshots** and **Elastic IPs** for lab leftovers. The pre-launch Sydney baseline had zero instances, volumes, snapshots and Elastic IPs. Do not delete unrelated resources.
3. Close the local SSH tunnel with Control+C. No SSH session or PHP process is expected to remain available after termination.
4. Review billing after usage data updates. A zero current bill or available credits does not establish that no resources remain. Stopping alone is not full cleanup: retained EBS storage can still incur charges.

### What I learned and limitations

I practised distinguishing local and remote terminals, verifying an SSH host fingerprint, using key authentication and restricted security-group rules, installing a PHP runtime, transferring code, running tests on a second PHP version, forwarding a local port through SSH, checking persistence, and correlating an HTTP error with a server log.

The PHP development server is a foreground learning tool, not a production web-server setup. There is no process supervisor, public HTTPS endpoint, application authentication, automated cloud provisioning, centralised logging, backup/recovery setup or production operations claim. Terminating the root volume removes its SQLite data. Private IPs, public IPs, account IDs and private-key contents are omitted from this evidence.

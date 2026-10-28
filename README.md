# Pritani — QA Portfolio Project

**A rice-mill operations web app built as a realistic system under test.**

![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-14%2B-4169E1?logo=postgresql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?logo=tailwindcss&logoColor=white)
![Playwright](https://img.shields.io/badge/Playwright-1.64-2EAD33?logo=playwright&logoColor=white)
[![Playwright Tests](https://github.com/Muhless/pg_pritani_qa/actions/workflows/playwright.yml/badge.svg)](https://github.com/Muhless/pg_pritani_qa/actions/workflows/playwright.yml)

Pritani is a Laravel + PostgreSQL application for a small rice mill (*penggilingan padi*). It records
grain purchases from farmers, mills grain into rice and by-products, sells the output to customers,
and keeps stock in sync at every step.

I built it as the subject of my QA practice. The interesting part of this repository is not only the
application, but **how it is tested**: risk-based test design, a Playwright end-to-end suite, CI on
every push and pull request, and documented bug investigations.

> The UI is in Indonesian because the domain is a real Indonesian rice mill. Code, tests, and this
> documentation are in English.

---

## Table of contents

- [Why this project exists](#why-this-project-exists)
- [Application overview](#application-overview)
- [Roles and access](#roles-and-access)
- [QA approach](#qa-approach)
- [Test coverage](#test-coverage)
- [Case study: phantom stock rows](#case-study-phantom-stock-rows)
- [Tech stack](#tech-stack)
- [Architecture and data model](#architecture-and-data-model)
- [Getting started](#getting-started)
- [Demo accounts](#demo-accounts)
- [Repository structure](#repository-structure)
- [Known gaps and roadmap](#known-gaps-and-roadmap)
- [About](#about)

---

## Why this project exists

Most QA portfolios use todo lists and login forms. I wanted a system with the properties that make
testing genuinely hard:

- **Money and inventory in the same workflow** — a wrong quantity or price propagates into stock,
  yield, and reports.
- **Multiple roles with different permissions**, so access control is part of the test surface.
- **Calculations with real consequences** — moisture deduction, milling yield, and stock corrections.
- **State that must stay consistent across modules** — purchases feed stock, milling consumes and
  produces stock, sales deduct stock.

The goal is to demonstrate the full QA loop on a non-trivial application: reading requirements,
prioritising risk, designing cases, automating the critical paths, reporting defects, and verifying
fixes with regression checks.

---

## Application overview

```text
Farmer ──► Grain purchase ──► Grain stock ──► Milling ──► Rice / Bran / Husk / Broken rice
                                                                        │
                                                          Customer ◄── Sale
```

| Module | What it does |
| --- | --- |
| **Dashboard** | Today's grain intake, rice stock, today's sales count, and average milling yield. |
| **Master data** | Farmers, customers, and item types (grain, rice, bran, husk, broken rice). |
| **Purchases** | Records grain bought from a farmer, including weight, price per kg, and moisture deduction. |
| **Milling** | Processes an unprocessed purchase into rice and by-products, with yield calculation. |
| **Sales** | Multi-item sales with cash, transfer, or credit payment, stock deduction, and a mark-as-paid action. |
| **Stock** | Per-item stock levels, low-stock filtering, and manual corrections with an audit trail. |
| **Auth** | Login, registration, remember-me, and role-based module access. |

---

## Roles and access

Permissions are enforced by `App\Http\Middleware\EnsureModuleAccess`, driven by `config/access.php`.
The sidebar only renders modules the current user can access; direct URL access returns `403`.

| Module | Admin | Operator Giling | Kasir |
| --- | :---: | :---: | :---: |
| Dashboard | ✅ | ✅ | ✅ |
| Purchases | ✅ | — | — |
| Milling | ✅ | ✅ | — |
| Sales | ✅ | — | ✅ |
| Stock | ✅ | ✅ | ✅ |
| Master data | ✅ | — | — |

This matrix is itself a test asset: every role × module pair is a candidate access-control case.

---

## QA approach

### Test strategy

Coverage is prioritised by business risk rather than by screen count:

1. **Integrity of money and stock** — purchase totals, moisture deduction, milling output limits,
   sales stock deduction, and stock corrections.
2. **Access control** — each role should see and reach only its modules.
3. **Boundary and negative paths** — zero and negative quantities, over-milling, missing due dates,
   invalid credentials, empty required fields.
4. **Core CRUD and navigation** — create, list, filter, paginate, and confirm the record appears.
5. **Exploratory sessions** — free-form passes over new features to find issues that scripted cases miss.

### What is automated

The end-to-end suite uses **Playwright + TypeScript** and targets the real application over HTTP.

| Concern | How it is handled |
| --- | --- |
| Deterministic data | `global-setup.ts` runs `migrate:fresh --seed` against the testing database before every suite run. |
| Authentication reuse | `e2e/auth.setup.ts` logs in once per role and persists `storageState`, so specs start authenticated. |
| Environment isolation | `webServer` boots the PHP built-in server with `APP_ENV=testing` on port `8001`. |
| Cross-browser | Chromium and Firefox projects; WebKit is scaffolded but currently disabled. |
| Reporting | Playwright HTML report, uploaded as a CI artifact on every run. |
| Page objects | Scaffolded in `e2e/pages/` to keep selectors out of specs as the suite grows. |

Representative test design decisions:

- Assertions target **user-visible behaviour** (headings, table rows, URL, error messages) rather
  than implementation details.
- Login is tested for all three roles, for invalid credentials, and for remember-me cookie behaviour.
- Created records are verified by searching for them in the rendered table, not by checking the
  database directly — the tests exercise the same path a user takes.

---

## Test coverage

Legend: ✅ automated · 🟡 partial · ⬜ manual only

| Area | Key scenarios | Automated |
| --- | --- | :---: |
| Authentication | Valid login (admin, operator, cashier), invalid credentials, remember me on/off | ✅ |
| Dashboard | Page renders, localized current date | ✅ |
| Farmers | List page, create farmer and see it in the table | ✅ |
| Customers | List page, create customer and see it in the table | ✅ |
| Authorization | Role-specific menu visibility and `403` on forbidden modules | 🟡 |
| Item types | Create, edit, delete, validation | ⬜ |
| Purchases | Net weight and total price calculation, moisture deduction, stock increase | ⬜ |
| Milling | Output limits versus input, yield calculation, by-product stock | ⬜ |
| Sales | Multi-item totals, stock deduction, credit due date, mark as paid | ⬜ |
| Stock | Low-stock filter, corrections, audit history, no negative stock | ⬜ |

Automated specs live in `e2e/`:

```text
e2e/
├── auth.setup.ts      # one-time login per role, saves storage state
├── auth.spec.ts       # login scenarios and remember-me behaviour
├── dashboard.spec.ts  # dashboard rendering
├── farmer.spec.ts     # farmer list and creation
├── customer.spec.ts   # customer list and creation
└── pages/             # page objects
```

### Running the suite

```bash
# one-time: create the test database (see .env.testing for credentials)
createdb db_pritani_e2e

npx playwright test                    # full suite (Chromium + Firefox)
npx playwright test --project=chromium # single browser
npx playwright test --ui               # interactive mode
npx playwright show-report             # open the last HTML report
```

`global-setup.ts` resets **only** the testing database before each run. Point `DB_DATABASE` in
`.env.testing` at a disposable database.

### Continuous integration

`.github/workflows/playwright.yml` installs dependencies and browsers, runs the suite on every push
and pull request to `main`/`master`, and uploads `playwright-report/` as an artifact (retained for
30 days). The badge at the top of this file reflects the latest run.

---

## Case study: phantom stock rows

**Symptom.** A stock update could appear to succeed while the quantity stayed unchanged, and the
stock list occasionally showed the same item twice.

**Investigation.** The `stocks` table was supposed to have a unique index on `item_type_id`, declared
in the original migration as:

```php
$table->foreignId('item_type_id')->constrained()->onDelete('restrict')->unique();
```

Inspection of the live schema showed only the primary key. The reason: `constrained()` returns a
`ForeignKeyDefinition` (a generic `Fluent` object), so the chained `unique()` was stored as an
irrelevant attribute and never reached the column definition. No unique index was ever created.

Because `StockController::update()` relies on `insertOrIgnore()` as a safety net for missing stock
rows, every update inserted another row — each with quantity `0` — and `firstOrFail()` could then
read a phantom row while another row held the real quantity. The database therefore drifted from
the application's assumption, silently.

**Fix.**

1. Reordered the migration so `unique()` applies to the column:
   `foreignId('item_type_id')->unique()->constrained()->onDelete('restrict')`.
2. Added a forward migration that merges duplicate rows (summing quantities into the oldest row) and
   then creates the unique index, with an idempotency check for fresh installs.
3. Verified the constraint exists after `migrate:fresh` and re-tested the update flow.

**Takeaways.** A schema assumption is only real if it is verified in the database, not just declared
in code. UI-level checks would never have caught this; it needed a query against `pg_indexes`.
This case is a good reminder that "the feature works" and "the data is correct" are different
assertions.

---

## Tech stack

| Layer | Choice |
| --- | --- |
| Language | PHP 8.3+ |
| Framework | Laravel 13 |
| Database | PostgreSQL |
| Views | Blade + Tailwind CSS 4 |
| Frontend behaviour | Alpine.js, SweetAlert2, Flatpickr |
| Build tool | Vite 8 |
| Test automation | Playwright 1.64 + TypeScript |
| CI | GitHub Actions |

---

## Architecture and data model

The application follows Laravel's MVC conventions. Routes in `routes/web.php` are grouped by module
and wrapped in the `EnsureModuleAccess` middleware, so a controller action is never reachable by a
role that does not own that module. Write operations that touch stock run inside database
transactions with `lockForUpdate()`.

Core tables:

```text
users, farmers, customers, item_types, purchases, milling_processes,
sales, sale_items, stocks, stock_movements
```

### Business rules worth testing

- **Net weight:** `weight_kg × (1 − moisture_deduction / 100)`.
- **Purchase total:** `net_weight_kg × price_per_kg`.
- **Milling yield:** `rice_output_kg / input_weight_kg × 100`.
- **Milling guard:** the sum of all outputs may not exceed the purchase's net weight.
- **Stock integrity:** stock may never become negative; sales and milling deduct stock atomically.
- **Low stock:** the UI flags items below `100 kg` (`Stock::LOW_STOCK_KG`).
- **Stock audit trail:** every manual correction stores type, amount, before/after quantities, an
  optional note, and the user who made it.
- **Sales limits:** at most 5 line items, at most `50,000 kg` per line, and a total cap of
  `Rp 9.999.999.999`; credit sales require a due date on or after the sale date.

---

## Getting started

### Prerequisites

- PHP 8.3+ with the `pdo_pgsql` extension
- Composer
- Node.js 20+ and npm
- PostgreSQL 14+

### Setup

```bash
git clone https://github.com/Muhless/pg_pritani_qa.git
cd pg_pritani_qa

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Create the database and point `.env` at it:

```bash
createdb db_pritani
# then set DB_DATABASE, DB_USERNAME, DB_PASSWORD in .env
```

Run the migrations and seed the demo users:

```bash
php artisan migrate --seed
```

Start the app (two terminals):

```bash
npm run dev        # Vite dev server
php artisan serve  # http://localhost:8000
```

For a production-style build, run `npm run build`.

> The seeder creates the three demo accounts below but no operational data. The dashboard and tables
> start empty by design, so the first run needs a few farmers, items, and purchases entered manually.

---

## Demo accounts

| Role | Email | Password | Can access |
| --- | --- | --- | --- |
| Admin | `admin@pritani.test` | `admin123` | Everything |
| Operator Giling | `operator@pritani.test` | `operator123` | Dashboard, Milling, Stock |
| Kasir | `kasir@pritani.test` | `kasir123` | Dashboard, Sales, Stock |

Seeded by `database/seeders/UserSeeder.php`. These credentials are for local evaluation only.

---

## Repository structure

```text
app/
├── Http/Controllers/     # one controller per module
├── Http/Middleware/      # EnsureModuleAccess (role → module guard)
└── Models/               # Eloquent models and business constants

config/access.php         # role × module permission matrix
database/
├── migrations/           # schema, including the stock audit trail
└── seeders/              # demo users

resources/views/          # Blade views (Indonesian UI)
routes/web.php            # module-grouped routes

e2e/                      # Playwright specs, setup, and page objects
global-setup.ts           # resets and seeds the test database
playwright.config.ts      # projects, web server, storage state
.github/workflows/        # CI pipeline
```

---

## Known gaps and roadmap

Documenting what is *not* covered yet is part of the QA discipline:

- [ ] Automate purchases, milling, sales, and stock corrections — the highest-risk remaining areas.
- [ ] Add explicit authorization tests for `403` responses on each role × module pair.
- [ ] Add PHPUnit feature tests for the calculation rules (net weight, totals, yield) so they run
      faster than E2E and closer to the code.
- [ ] Add a seeding profile with realistic operational data so reviewers can explore immediately.
- [ ] Enable the WebKit project and add mobile viewport runs.
- [ ] Add accessibility checks and visual regression snapshots.
- [ ] Add traceability artifacts: a test plan, a case catalogue, and bug report templates.

---

## About

Built and tested by **[Muhless](https://github.com/Muhless)** as a QA portfolio project.

- Repository: [github.com/Muhless/pg_pritani_qa](https://github.com/Muhless/pg_pritani_qa)
- Stack: Laravel, PostgreSQL, Tailwind CSS, Playwright
- Focus: end-to-end testing of money- and inventory-critical workflows

The application contains no production data. Feedback on test design and coverage is welcome via
GitHub issues.

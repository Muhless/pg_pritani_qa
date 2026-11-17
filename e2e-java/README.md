# Pritani E2E — Selenium Java

End-to-end test **scaffold** for the Pritani Laravel application, built with **Java 21**, **Selenium
4**, and **TestNG**. Add your own page objects and test classes on top of it; the plumbing (browser
lifecycle, configuration, database reset, CI) is already wired.

## Prerequisites

- Java 21+
- Maven 3.9+
- A browser. Chrome/Chromium is preferred; when no browser is installed, Selenium Manager downloads
  Chrome for Testing automatically.
- PHP 8.3+ with `pdo_pgsql`, plus PostgreSQL binaries (`initdb`, `pg_ctl`, `psql`, `createdb`).
- Composer dependencies installed, and Node.js for the frontend build.

## Quick start

From the repository root:

```bash
./e2e-java/run-e2e.sh          # run the suite (default)
./e2e-java/run-e2e.sh serve    # start database + app and keep them running
./e2e-java/run-e2e.sh db       # start the e2e database only
./e2e-java/run-e2e.sh db:stop  # stop the e2e database
```

Use `serve` when you want to browse the app manually or write tests against it:

```bash
./e2e-java/run-e2e.sh serve
# app:  http://127.0.0.1:8001
# then, from e2e-java/: mvn test
```

The `test` command:

1. Creates `.env.e2e` from `.env.e2e.example` (`APP_ENV=e2e`).
2. Starts a **disposable Postgres cluster** in `database/.pgsql-e2e` on port `55432` (no sudo).
3. Builds frontend assets with `npm run build` when `public/build/manifest.json` is missing.
4. Runs `migrate:fresh --seed` against `db_pritani_e2e`.
5. Starts the app on `http://127.0.0.1:8001`.
6. Runs `mvn test` and stops the app and database afterwards.

A database started by `db` (or one that was already running) is **not** stopped by `serve` or `test`;
only a database that the command started itself is cleaned up.

With no tests yet, the suite finishes with zero tests. Add a test class and rerun.

## Running against an already running app

```bash
./e2e-java/run-e2e.sh serve
# in another terminal, from e2e-java/
mvn test -DbaseUrl=http://127.0.0.1:8001
```

Do not start `php artisan serve --env=e2e` manually while the database is stopped: the app uses
`SESSION_DRIVER=database`, so every request fails with `connection refused` on port `55432`.

Options (system properties, or the matching environment variables):

| Property | Environment | Default |
| --- | --- | --- |
| `baseUrl` | `E2E_BASE_URL` | `http://127.0.0.1:8001` |
| `browser` | `E2E_BROWSER` | `chrome` (`chrome`, `firefox`, `edge`) |
| `headless` | `E2E_HEADLESS` | `true` |
| `timeout` | `E2E_TIMEOUT` | `15` (seconds) |

Show the browser:

```bash
mvn test -Dheadless=false
```

Run a single class:

```bash
mvn test -Dtest=AuthTest
```

## What is already provided

```text
e2e-java/
├── pom.xml
├── testng.xml                     # suite definition, parallel classes
├── run-e2e.sh                     # database + app + suite
├── .env.e2e.example
└── src/test/java/id/pritani/e2e/
    ├── base/BaseTest.java         # driver lifecycle, wait, visit(), failure screenshots
    ├── config/                    # base URL, browser, demo accounts
    ├── driver/DriverFactory.java  # Chrome/Firefox/Edge factory
    ├── pages/BasePage.java        # shared page-object helpers (waits, click, type, table rows)
    └── support/XPath.java         # safe XPath string literals
```

`BaseTest` gives every test a `driver`, a `wait` (`WebDriverWait` using the configured timeout), and
`visit("/path")`. Failed tests save a screenshot to `target/screenshots/`.

`BasePage` extends this to page objects: `visible`, `present`, `clickable`, `click`, `type`, `text`,
`selectByText`, `isVisible`, `waitUntilGone`, `waitForPath`, and table-row helpers such as
`tableCell("Adam Wharton")`.

Demo accounts are available through `Accounts.ADMIN`, `Accounts.OPERATOR`, and `Accounts.CASHIER`.

## Suggested structure for your own tests

```text
src/test/java/id/pritani/e2e/
├── pages/                         # page objects extending BasePage
│   └── LoginPage.java
└── tests/                         # TestNG classes extending BaseTest
    └── AuthTest.java
```

`testng.xml` scans the `id.pritani.e2e.tests` package, so any class placed there runs automatically.

## Writing your first test

```java
package id.pritani.e2e.tests;

import id.pritani.e2e.base.BaseTest;
import id.pritani.e2e.config.Accounts;
import org.openqa.selenium.By;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.testng.Assert;
import org.testng.annotations.Test;

public class AuthTest extends BaseTest {

    @Test
    public void adminCanLogin() {
        visit("/login");

        wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("login_email")))
                .sendKeys(Accounts.ADMIN.email());
        driver.findElement(By.id("login_password")).sendKeys(Accounts.ADMIN.password());
        driver.findElement(By.cssSelector("button[type='submit']")).click();

        wait.until(ExpectedConditions.urlMatches(".*(?<!/login)$"));

        Assert.assertEquals(wait.until(ExpectedConditions.visibilityOfElementLocated(
                By.xpath("//h1[normalize-space()='Ringkasan Hari Ini']"))).getText(),
                "Ringkasan Hari Ini");
    }
}
```

Guidelines:

1. Prefer explicit waits (`wait.until(...)`) over `Thread.sleep`.
2. Move repeated locators into a page object under `pages/`.
3. Use unique data (for example a timestamp suffix) so tests can run repeatedly without a reset.
4. Assert on user-visible behaviour (headings, table rows, URL, error messages).

## Database modes

- Default (`E2E_DB_MODE=local`): the script manages a disposable cluster in `database/.pgsql-e2e`.
- `E2E_DB_MODE=external`: use an existing database; configure `E2E_DB_HOST`, `E2E_DB_PORT`,
  `E2E_DB_NAME`, `E2E_DB_USER`, and `E2E_DB_PASSWORD`. This is how CI runs against a Postgres
  service container.

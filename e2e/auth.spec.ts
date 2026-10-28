import test, { expect, test as setup } from "@playwright/test";

test.use({ storageState: { cookies: [], origins: [] } });

test("Login with valid email and password", async ({ page }) => {
  await page.goto("/login");
  await page.getByLabel("Email").fill("admin@pritani.test");
  await page.locator("#login_password").fill("admin123");
  await page.getByRole("button", { name: "Masuk" }).click();
  await expect(page).toHaveURL("/");
});

test("Login with role operator", async ({ page }) => {
  await page.goto("/login");
  await page.getByLabel("Email").fill("operator@pritani.test");
  await page.locator("#login_password").fill("operator123");
  await page.getByRole("button", { name: "Masuk" }).click();
  await expect(page).toHaveURL("/");
});

test("Login with role kasir", async ({ page }) => {
  await page.goto("/login");
  await page.getByLabel("Email").fill("kasir@pritani.test");
  await page.locator("#login_password").fill("kasir123");
  await page.getByRole("button", { name: "Masuk" }).click();
  await expect(page).toHaveURL("/");
});

test("Login with invalid email and password", async ({ page }) => {
  await page.goto("/login");
  await page.getByLabel("Email").fill("admin@pritani.com");
  await page.locator("#login_password").fill("admin111");
  await page.getByRole("button", { name: "Masuk" }).click();
  await expect(page.getByText("Email atau password salah")).toBeVisible();
});

const users = [
  { role: "admin", email: "admin@pritani.test", password: "admin123" },
  { role: "operator", email: "operator@pritani.test", password: "operator123" },
  { role: "kasir", email: "kasir@pritani.test", password: "kasir123" },
];
for (const user of users) {
  setup(
    `Token created after successfully login as ${user.role}`,
    async ({ page }) => {
      await page.goto("/login");
      await page.getByLabel("Email").fill(user.email);
      await page.locator("#login_password").fill(user.password);
      await page.getByRole("button", { name: "Masuk" }).click();
      await page.waitForURL("/");
      await page
        .context()
        .storageState({ path: `playwright/.auth/${user.role}.json` });
    },
  );
}

test("Login with remember me checked", async ({ page, context }) => {
  await page.goto("/login");
  await page.getByLabel("Email").fill("admin@pritani.test");
  await page.locator("#login_password").fill("admin123");
  await page.getByLabel("Ingat saya").check();
  await page.getByRole("button", { name: "Masuk" }).click();
  await expect(page).toHaveURL("/");

  const cookies = await context.cookies();
  const rememberCookie = cookies.find(
    (cookie) => cookie.name.includes("remember") || cookie.name.includes("REMEMBER")
  );
  expect(rememberCookie).toBeDefined();
});

test("Login without remember me", async ({ page, context }) => {
  await page.goto("/login");
  await page.getByLabel("Email").fill("admin@pritani.test");
  await page.locator("#login_password").fill("admin123");
  await page.getByRole("button", { name: "Masuk" }).click();
  await expect(page).toHaveURL("/");

  const cookies = await context.cookies();
  const rememberCookie = cookies.find(
    (cookie) => cookie.name.includes("remember") || cookie.name.includes("REMEMBER")
  );
  expect(rememberCookie).toBeUndefined();
});

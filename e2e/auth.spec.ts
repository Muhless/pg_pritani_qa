import test, { expect, test as setup } from "@playwright/test";

test.use({ storageState: { cookies: [], origins: [] } });

test("Login with valid email and password", async ({ page }) => {
  await page.goto("/login");
  await page.getByLabel("email").fill("admin@pritani.test");
  await page.getByLabel("password").fill("admin123");
  await page.getByRole("button", { name: "masuk" }).click();
  await expect(page).toHaveURL("/");
});

test("Login with role operator", async ({ page }) => {
  await page.goto("http://localhost:8001/login");
  await page.getByLabel("email").fill("operator@pritani.test");
  await page.getByLabel("password").fill("operator123");
  await page.getByRole("button", { name: "masuk" }).click();
  await expect(page).toHaveURL("http://localhost:8001/");
});

test("Login with role kasir", async ({ page }) => {
  await page.goto("http://localhost:8001/login");
  await page.getByLabel("email").fill("kasir@pritani.test");
  await page.getByLabel("password").fill("kasir123");
  await page.getByRole("button", { name: "masuk" }).click();
  await expect(page).toHaveURL("http://localhost:8001/");
});

test("Login with invalid email and password", async ({ page }) => {
  await page.goto("http://localhost:8001/login");
  await page.getByLabel("email").fill("admin@pritani.com");
  await page.getByLabel("password").fill("admin111");
  await page.getByRole("button", { name: "masuk" }).click();
  await expect(page.getByText("email atau password salah")).toBeVisible();
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
      await page.getByLabel("email").fill(user.email);
      await page.getByLabel("password").fill(user.password);
      await page.getByRole("button", { name: "masuk" }).click();
      await page.waitForURL("/");
      await page
        .context()
        .storageState({ path: `playwright/.auth/${user.role}.json` });
    },
  );
}

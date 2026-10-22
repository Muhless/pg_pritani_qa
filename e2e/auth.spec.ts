import test, { expect } from "@playwright/test";

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

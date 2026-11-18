import test from "@playwright/test";

test("Login with valid data", async ({ page }) => {
  await page.goto("http://127.0.0.1:8000/login");
  await page.getByLabel("Email").fill("admin@pritani.test");
  await page.getByLabel("Password", { exact : true }).fill("admin123");
});

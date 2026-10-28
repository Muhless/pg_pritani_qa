import { expect, test as setup } from "@playwright/test";

setup("Login as admin", async ({ page }) => {
  await page.goto("/login");
  await page.getByLabel("Email").fill("admin@pritani.test");
  await page.locator("#login_password").fill("admin123");
  await page.getByRole("button", { name: "Masuk" }).click();
  await expect(page).toHaveURL("/");
  await page.context().storageState({ path: ".auth/admin.json" });
});

setup("Login as operator", async ({ page }) => {
  await page.goto("/login");
  await page.getByLabel("Email").fill("operator@pritani.test");
  await page.locator("#login_password").fill("operator123");
  await page.getByRole("button", { name: "Masuk" }).click();
  await expect(page).toHaveURL("/");
  await page.context().storageState({ path: ".auth/operator.json" });
});

setup("Login as kasir", async ({ page }) => {
  await page.goto("/login");
  await page.getByLabel("Email").fill("kasir@pritani.test");
  await page.locator("#login_password").fill("kasir123");
  await page.getByRole("button", { name: "Masuk" }).click();
  await expect(page).toHaveURL("/");
  await page.context().storageState({ path: ".auth/kasir.json" });
});

import { expect, test } from "@playwright/test";

test("Dashboard tampil setelah login", { tag: "@smoke" }, async ({ page }) => {
  await page.goto("/");
  await expect(page).not.toHaveURL(/\/login/);
});

import test, { expect } from "@playwright/test";

test("Header title is displayed", async ({ page }) => {
  await page.goto("/");
  await expect(page.getByRole("heading", { name: "Ringkasan Hari Ini" }))
    .toBeVisible();
});

test("Today's date is displayed", async ({ page }) => {
  const today = new Intl.DateTimeFormat("id-ID", {
    dateStyle: "full",
    timeZone: "Asia/Jakarta",
  }).format(new Date());
  await page.goto("/");
  await expect(page.getByText(today)).toBeVisible();
});

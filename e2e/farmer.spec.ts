import test, { expect } from "@playwright/test";

test("Header title is displayed", async ({ page }) => {
  await page.goto("/farmers");
  await expect(page.getByRole("heading", { name: "Petani" })).toBeVisible();
});

test("Create farmer data", async ({ page }) => {
  await page.goto("/farmers");
  await page.getByRole("button", { name: "Tambah Petani" }).click();
  await page.getByLabel("Nama Lengkap").fill("Adam Wharton");
  await page.getByLabel("NIK").fill("1112223334445556");
  await page.getByLabel("Nomor Telepon").fill("088711165552");
  await page.getByLabel("Alamat").fill("Kp. Durian runtuh, blok J12");
  await page.getByRole("button", { name: "Simpan" }).click();
  await expect(page.getByText("Adam Wharton")).toBeVisible();
});

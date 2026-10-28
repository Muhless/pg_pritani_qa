import test, { expect } from "@playwright/test";

test("Customer heading is displayed", async ({ page }) => {
  await page.goto("/customers");
  await expect(page.getByRole("heading", { name: "Pelanggan" })).toBeVisible();
});

test("Create customer data", async ({ page }) => {
  await page.goto("/customers");
  await page.getByRole("button", { name: "Tambah Pelanggan" }).click();
  await page.getByLabel("Nama Pelanggan").fill("Michael Carrick");
  await page.getByLabel("Jenis Pelanggan").selectOption({ label: "Toko" });
  await page.getByLabel("Nomor Telepon").fill("08871165552");
  await page.getByLabel("Alamat").fill("Cangkudu Balaraja");
  await page.getByRole("button", { name: "Simpan" }).click();
  await expect(
    page.getByRole("row", { name: /Michael Carrick/ }),
  ).toBeVisible();
});

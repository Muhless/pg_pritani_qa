import { expect, Locator, Page } from "@playwright/test";

export class FarmerPage {
  readonly page: Page;
  readonly title: Locator;
  readonly addButton: Locator;
  readonly nameInput: Locator;
  readonly nikInput: Locator;
  readonly phoneInput: Locator;
  readonly addressInput: Locator;
  readonly submitButton: Locator;
  readonly successMessage: Locator;

  constructor(page: Page) {
    this.page = page;
    this.title = page.getByRole("heading", { name: "Petani" });
    this.addButton = page.getByRole("button", { name: "Tambah Petani" });
    this.nameInput = page.getByLabel("Nama Lengkap");
    this.nikInput = page.getByLabel("NIK");
    this.phoneInput = page.getByLabel("Nomor Telepon");
    this.addressInput = page.getByLabel("Alamat");
    this.submitButton = page.getByRole("button", { name: "Simpan" });
    this.successMessage = page.getByRole("alert");
  }

  async goTo() {
    await this.page.goto("/farmers");
    await expect(this.title).toBeVisible();
  }

  async fillForm(name: string, nik: string, phone: string, address: string) {
    await this.nameInput.fill(name);
    await this.nikInput.fill(nik);
    await this.phoneInput.fill(phone);
    await this.addressInput.fill(address);
  }

  async submit() {
    await this.submitButton.click();
  }

  async addFarmer(name: string, nik: string, phone: string, address: string) {
    await this.addButton.click();
    await this.fillForm(name, nik, phone, address);
    await this.submit();
  }

  async expectFarmerAdded(name: string) {
    await expect(
      this.page.getByRole("row", { name: new RegExp(name) }),
    ).toBeVisible();
  }
}

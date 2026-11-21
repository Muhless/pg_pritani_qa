import { expect, Locator, Page } from "@playwright/test";

export class CustomerPage {
  readonly page: Page;
  readonly title: Locator;
  readonly addButton: Locator;
  readonly editButton: Locator;
  readonly deleteButton: Locator;
  readonly nameInput: Locator;
  readonly typeSelect: Locator;
  readonly phoneInput: Locator;
  readonly addressInput: Locator;
  readonly submitButton: Locator;
  readonly confirmDialog: Locator;
  readonly confirmDeleteButton: Locator;
  readonly cancelDeleteButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.title = page.getByRole("heading", { name: "Pelanggan" });
    this.addButton = page.getByRole("button", { name: "Tambah Pelanggan" });
    this.editButton = page.getByRole("button", { name: "Edit" });
    this.deleteButton = page.getByRole("button", { name: "Hapus" });
    this.nameInput = page.getByLabel("Nama Pelanggan");
    this.typeSelect = page.getByLabel("Jenis Pelanggan");
    this.phoneInput = page.getByLabel("Nomor Telepon");
    this.addressInput = page.getByLabel("Alamat");
    this.submitButton = page.getByRole("button", { name: "Simpan" });

    this.confirmDialog = page.getByRole("dialog");
   this.confirmDeleteButton = this.confirmDialog.getByRole("button", {
     name: /ya,?\s*hapus/i,
   });
    this.cancelDeleteButton = this.confirmDialog.getByRole("button", {
      name: "Batal",
    });
  }

  async goTo() {
    await this.page.goto("/customers");
    await expect(this.title).toBeVisible();
  }

  async openModal() {
    await this.addButton.click();
  }

  async fillModal(name: string, type: string, phone: string, address: string) {
    await this.nameInput.fill(name);
    await this.typeSelect.selectOption(type);
    await this.phoneInput.fill(phone);
    await this.addressInput.fill(address);
  }

  async submitModal() {
    await this.submitButton.click();
  }

  async expectedCustomerAdded(name: string) {
    await expect(
      this.page.getByRole("row", { name: new RegExp(name) }),
    ).toBeVisible();
  }

  async deleteCustomer(name: string) {
    const row = this.page.getByRole("row", { name });
    await row.getByRole("button", { name: "Hapus" }).click();
    await this.confirmDialog.waitFor({ state: "visible" });
    await this.confirmDeleteButton.click();
  }

  async cancelDeleteCustomer() {
    await this.deleteButton.click();
    await this.cancelDeleteButton.click();
  }
}

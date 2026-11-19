import { expect, Locator, Page } from "@playwright/test";

export class ProductPage {
  readonly page: Page;
  readonly title: Locator;
  readonly addButton: Locator;
  readonly nameInput: Locator;
  readonly categorySelect: Locator;
  readonly unitInput: Locator;
  readonly submitButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.title = page.getByRole("heading", { name: "Jenis Barang" });
    this.addButton = page.getByRole("button", { name: "Tambah Jenis Barang" });
    this.nameInput = page.getByLabel("Nama Barang");
    this.categorySelect = page.getByLabel("Kategori Barang");
    this.unitInput = page.getByLabel("Satuan");
    this.submitButton = page.getByRole("button", { name: "Simpan" });
  }

  async goTo() {
    await this.page.goto("/item-types");
    await expect(this.title).toBeVisible();
  }

  async selectCategory(category: string) {
    await this.categorySelect.selectOption({ label: category });
  }

  async openAddForm() {
    await this.addButton.click();
  }
 
  async fillForm(name: string, category: string, unit: string) {
    await this.nameInput.fill(name);
    await this.selectCategory(category);
    await this.unitInput.fill(unit);
  }

  async submit() {
    await this.submitButton.click();
  }

  async addProduct(name: string, category: string, unit: string) {
    await this.addButton.click();
    await this.fillForm(name, category, unit);
    await this.submit();
  }

  async expectProductAdded(name: string) {
    await expect(
      this.page.getByRole("row", { name: new RegExp(name) }),
    ).toBeVisible();
  }
}

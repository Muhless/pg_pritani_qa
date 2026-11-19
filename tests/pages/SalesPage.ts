import { expect, Locator, Page } from "@playwright/test";

export type SalesItem = {
  item: string;
  weight: string;
  price: string;
};

export class SalesPage {
  readonly page: Page;
  readonly title: Locator;
  readonly addButton: Locator;
  readonly customerSelect: Locator;
  readonly dateSelect: Locator;
  readonly paymentSelect: Locator;
  readonly addMoreItemButton: Locator;
  readonly submitButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.title = page.getByRole("heading", { name: "Penjualan" });
    this.addButton = page.getByRole("button", { name: "Tambah Penjualan" });
    this.customerSelect = page.getByLabel("Nama Pelanggan");
    this.dateSelect = page.getByLabel("Tanggal Penjualan");
    this.paymentSelect = page.getByLabel("Metode Pembayaran");
    this.addMoreItemButton = page.getByRole("button", {
      name: "Tambah Barang",
    });
    this.submitButton = page.getByRole("button", { name: "Simpan" });
  }

  private itemSelect(index: number) {
    return this.page.getByLabel(`Barang ke-${index + 1}`, { exact: true });
  }

  private weightInput(index: number) {
    return this.page.getByLabel(`Berat ke-${index + 1} (kg)`, { exact: true });
  }

  private priceInput(index: number) {
    return this.page.getByLabel(`Harga ke-${index + 1} (Rp/kg)`, {
      exact: true,
    });
  }

  async goTo() {
    await this.page.goto("/sales");
    await expect(this.title).toBeVisible();
  }

  async openForm() {
    await this.addButton.click();
  }

  async fillItem(index: number, data: SalesItem) {
    await this.itemSelect(index).selectOption({ label: data.item });
    await this.weightInput(index).fill(data.weight);
    await this.priceInput(index).fill(data.price);
  }

  async fillForm(
    customer: string,
    date: string,
    payment: string,

    items: SalesItem[],
  ) {
    await this.customerSelect.selectOption({ label: customer });
    await this.dateSelect.fill(date);
    await this.paymentSelect.selectOption({ label: payment });
    for (const [i, data] of items.entries()) {
      if (i > 0) {
        await this.addMoreItemButton.click();
      }
      await this.fillItem(i, data);
    }
  }

  async submit() {
    await this.submitButton.click();
  }

  async fillDate(date: Date) {
    const iso = date.toISOString().slice(0, 10);
    await this.dateSelect.fill(iso);
  }

  async expectSalesAdded(customerName: string) {
    await expect(
      this.page.getByRole("row", { name: new RegExp(customerName) }),
    ).toBeVisible();
  }
}

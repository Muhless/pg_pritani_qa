import { test } from "@playwright/test";
import { createCustomer, createItemWithStock } from "./helpers/api";
import { today } from "./helpers/date";
import { SalesPage } from "./pages/SalesPage";

test("Add sales", { tag: "@smoke" }, async ({ page, request }) => {
  const id = Date.now();
  const customerName = `Customer ${id}`;
  const item1 = `Beras Pulen ${id}`;
  const item2 = `Beras IR64 ${id}`;

  // Prasyarat lewat API
  await createCustomer(request, { name: customerName, phone: "081234567812" });
  await createItemWithStock(request, {
    name: item1,
    category: "rice",
    quantity_kg: 500,
  });
  await createItemWithStock(request, {
    name: item2,
    category: "rice",
    quantity_kg: 500,
  });

  // Skenario yang dites lewat UI
  const salesPage = new SalesPage(page);
  await salesPage.goTo();
  await salesPage.openForm();
  await salesPage.fillForm(customerName, today(), "Tunai", [
    { item: item1, weight: "10", price: "15000" },
    { item: item2, weight: "15", price: "20000" },
  ]);
  await page.pause();
  await salesPage.submit();
  await salesPage.expectSalesAdded(customerName);
});

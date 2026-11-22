import test, { expect } from "@playwright/test";
import { createCustomer } from "./helpers/api";
import { CustomerPage } from "./pages/CustomerPage";

test("Add customer data", async ({ page }) => {
  const customerPage = new CustomerPage(page);
  const name = `Customer ${Date.now()}`;

  await customerPage.goTo();
  await customerPage.openModal();
  await customerPage.fillModal(name, "Toko", "08871165522", "Kp.Juanda No.02");
  await customerPage.submitModal();

  await customerPage.expectedCustomerAdded(name);
});

test("Edit customer data", async ({ page, request }) => {
  const customerName = `customer ${Date.now()}`;
  const editedName = `${customerName} edited`;
  await createCustomer(request, { name: customerName, phone: "08877171717" });
  const customerPage = new CustomerPage(page);

  await customerPage.goTo();
  await customerPage.editCustomer(customerName, {
    name: editedName,
    type: "Toko",
    phone: "08871165553",
    address: "Kp.Kiara",
  });

  await expect(page.getByRole("row", { name: editedName })).toBeVisible();
  await expect(
    page.getByRole("row", { name: customerName, exact: true }),
  ).toHaveCount(0);
});

test("Delete customer data", async ({ page, request }) => {
  const customerName = `customer ${Date.now()}`;
  await createCustomer(request, { name: customerName, phone: "007781165551" });
  const customerPage = new CustomerPage(page);

  await customerPage.goTo();
  await customerPage.deleteCustomer(customerName);

  await expect(page.getByRole("row", { name: customerName })).toHaveCount(0);
});

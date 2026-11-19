import test from "@playwright/test";
import { ProductPage } from "./ProductPage";

test("Add product type", async ({ page }) => {
  const productPage = new ProductPage(page);
  const name = `Product ${Date.now()}`;

  await productPage.goTo();
  await productPage.openAddForm();
  await productPage.fillForm(name, "Beras", "Kg");
  await productPage.submit();

  await productPage.expectProductAdded(name);
});

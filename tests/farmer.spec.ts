import test from "@playwright/test";
import { FarmerPage } from "./pages/FarmerPage";

test("Add farmer", async ({ page }) => {
  const farmerPage = new FarmerPage(page);
  const name = `Farmer ${Date.now()}`;

  await farmerPage.goTo();
  await farmerPage.addFarmer(
    name,
    "3201234567890001",
    "081234567890",
    "Jl. Sukarasa",
  );
  await farmerPage.expectFarmerAdded(name);
});

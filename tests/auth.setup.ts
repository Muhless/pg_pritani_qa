import { test as setup } from "@playwright/test";
import { LoginPage } from "./pages/LoginPage";

const authFile = "playwright/.auth/admin.json";

setup("Login as admin", async ({ page }) => {
  const loginPage = new LoginPage(page);
  await loginPage.goto();
  await loginPage.login("admin@pritani.test", "admin123");
  await loginPage.expectLoggedIn();

  await page.context().storageState({ path: authFile });
});

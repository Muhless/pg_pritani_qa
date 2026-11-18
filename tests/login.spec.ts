import test, { expect } from "@playwright/test";
import { LoginPage } from "./pages/LoginPage";

test.use({ storageState: { cookies: [], origins: [] } });

test("Login with valid data", { tag: "@smoke" }, async ({ page }) => {
  const loginPage = new LoginPage(page);
  await loginPage.goto();
  await loginPage.login("admin@pritani.test", "admin123");
  await loginPage.expectLoggedIn();
});

test("Login as operator", async ({ page }) => {
  const loginPage = new LoginPage(page);
  await loginPage.goto();
  await loginPage.login("operator@pritani.test", "operator123");
  await loginPage.expectLoggedIn();
});

test("Login as kasir", async ({ page }) => {
  const loginPage = new LoginPage(page);
  await loginPage.goto();
  await loginPage.login("kasir@pritani.test", "kasir123");
  await loginPage.expectLoggedIn();
});

const InvalidCases = [
  {
    name: "Format email tidak valid",
    email: "admin",
    password: "admin123",
  },
  {
    name: "Password wajib diisi",
    email: "admin@pritani.test",
    password: "",
  },
  {
    name: "Email atau password salah.",
    email: "admin@gmail.com",
    password: "admin123",
  },
];

for (const c of InvalidCases) {
  test(`Login failed: ${c.name}`, async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(c.email, c.password);

    await loginPage.expectedStayOnLogin();
    await expect(loginPage.errorMessage).toBeVisible();
  });
}

test("Login gagal: field kosong", async ({ page }) => {
  const loginPage = new LoginPage(page);
  await loginPage.goto();
  await loginPage.submitButton.click();

  await loginPage.expectedStayOnLogin();
});

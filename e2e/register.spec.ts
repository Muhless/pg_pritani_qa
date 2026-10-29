import test from "@playwright/test";
import { validUser } from "./test-data/user";

test.use({ storageState: { cookies: [], origins: [] } });

test("Navigate to register pages", async ({ page }) => {
  await page.goto("/register");
});



test('Input register form', async ({page})=>{
     await page.getByLabel("Nama", {exact:true}).fill(validUser.name)
     await page.getByLabel("Email").fill(validUser.createEmail)
     await page.getByLabel("Password").fill()
     await page.getByLabel("Konfirmasi Password").fill("")
     await page.getByRole("button", { name: "Daftar" }).click();

     await expect(page.()).
})

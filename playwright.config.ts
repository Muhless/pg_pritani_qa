import { defineConfig, devices } from "@playwright/test";

export default defineConfig({
  testDir: "./e2e",
  globalSetup: "./global-setup.ts",
  use: {
    baseURL: "http://localhost:8001",
  },
  projects: [
    {
      name: "setup",
      testMatch: /.*\.setup\.ts/,
      use: {
        launchOptions: { executablePath: process.env.CHROMIUM_PATH },
      },
    },
    {
      name: "auth",
      testMatch: /login\.spec\.ts/,
      use: { ...devices["Desktop Chrome"] },
    },
    {
      name: "chromium",
      testIgnore: /.*\.setup\.ts/,
      dependencies: ["setup"],
      use: {
        ...devices["Desktop Chrome"],
        storageState: ".auth/admin.json",
        launchOptions: { executablePath: process.env.CHROMIUM_PATH },
      },
    },
    {
      name: "firefox",
      testIgnore: /.*\.setup\.ts/,
      dependencies: ["setup"],
      use: { ...devices["Desktop Firefox"], storageState: ".auth/admin.json" },
    },
    // {
    //   name: "webkit",
    //   testIgnore: /.*\.setup\.ts/,
    //   dependencies: ["setup"],
    //   use: { ...devices["Desktop Safari"], storageState: ".auth/admin.json" },
    // },
  ],
  webServer: {
    command: "php artisan serve --port=8001 --env=testing",
    url: "http://localhost:8001",
    reuseExistingServer: true,
  },
});

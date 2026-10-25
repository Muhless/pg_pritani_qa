// e2e/db.setup.ts
import { test as setup } from "@playwright/test";
import { execSync } from "child_process";

setup("reset database", async () => {
  execSync("php artisan migrate:fresh --seed --env=e2e", { stdio: "inherit" });
});

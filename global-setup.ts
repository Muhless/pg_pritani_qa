import { execSync } from "child_process";

export default async function globalSetup() {
  execSync("php artisan migrate:fresh --seed --env=e2e", {
    stdio: "inherit",
  });
}

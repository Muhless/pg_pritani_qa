import { execSync } from "child_process";
import { readFileSync } from "fs";

export default async function globalSetup() {
  const env = readFileSync(".env.testing", "utf8");
  if (!/DB_DATABASE=.*test/.test(env)) {
    throw new Error("DB_DATABASE di .env.testing harus berisi kata 'test'");
  }
  execSync("php artisan migrate:fresh --seed --env=testing", {
    stdio: "inherit",
  });
}

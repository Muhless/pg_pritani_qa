package id.pritani.e2e.config;

/**
 * Demo accounts created by database/seeders/UserSeeder.php.
 */
public final class Accounts {

    public static final Account ADMIN = new Account("admin@pritani.test", "admin123");
    public static final Account OPERATOR = new Account("operator@pritani.test", "operator123");
    public static final Account CASHIER = new Account("kasir@pritani.test", "kasir123");

    private Accounts() {
    }
}

package id.pritani.e2e.config;

import java.time.Duration;

/**
 * Central configuration, resolved from system properties first, then environment
 * variables, then defaults. Maven passes the properties defined in pom.xml.
 */
public final class Config {

    private Config() {
    }

    public static String baseUrl() {
        return read("baseUrl", "E2E_BASE_URL", "http://127.0.0.1:8001").replaceAll("/+$", "");
    }

    public static String browser() {
        return read("browser", "E2E_BROWSER", "chrome").toLowerCase();
    }

    public static boolean headless() {
        return Boolean.parseBoolean(read("headless", "E2E_HEADLESS", "true"));
    }

    public static Duration timeout() {
        return Duration.ofSeconds(Long.parseLong(read("timeout", "E2E_TIMEOUT", "15")));
    }

    private static String read(String property, String environment, String fallback) {
        String value = System.getProperty(property);

        if (value == null || value.isBlank()) {
            value = System.getenv(environment);
        }

        return value == null || value.isBlank() ? fallback : value.trim();
    }
}

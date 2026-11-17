package id.pritani.e2e.driver;

import id.pritani.e2e.config.Config;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.edge.EdgeDriver;
import org.openqa.selenium.edge.EdgeOptions;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.openqa.selenium.firefox.FirefoxOptions;

/**
 * Creates a WebDriver instance. Selenium Manager resolves the matching browser
 * driver automatically, and downloads Chrome for Testing when no browser is
 * installed on the machine.
 */
public final class DriverFactory {

    private DriverFactory() {
    }

    public static WebDriver create() {
        return switch (Config.browser()) {
            case "firefox" -> firefox();
            case "edge" -> edge();
            default -> chrome();
        };
    }

    private static WebDriver chrome() {
        ChromeOptions options = new ChromeOptions();
        options.addArguments("--window-size=1440,900");
        options.addArguments("--disable-dev-shm-usage");
        options.addArguments("--no-sandbox");
        options.addArguments("--disable-gpu");

        if (Config.headless()) {
            options.addArguments("--headless=new");
        }

        return new ChromeDriver(options);
    }

    private static WebDriver firefox() {
        FirefoxOptions options = new FirefoxOptions();

        if (Config.headless()) {
            options.addArguments("-headless");
        }

        return new FirefoxDriver(options);
    }

    private static WebDriver edge() {
        EdgeOptions options = new EdgeOptions();

        if (Config.headless()) {
            options.addArguments("--headless=new");
        }

        return new EdgeDriver(options);
    }
}

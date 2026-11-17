package id.pritani.e2e.base;

import id.pritani.e2e.config.Config;
import id.pritani.e2e.driver.DriverFactory;
import org.openqa.selenium.OutputType;
import org.openqa.selenium.TakesScreenshot;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;

import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.time.Duration;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;

public abstract class BaseTest {

    protected WebDriver driver;
    protected WebDriverWait wait;

    @BeforeMethod(alwaysRun = true)
    public void openBrowser() {
        driver = DriverFactory.create();
        driver.manage().timeouts().pageLoadTimeout(Config.timeout());
        driver.manage().timeouts().implicitlyWait(Duration.ZERO);
        driver.manage().window().maximize();

        wait = new WebDriverWait(driver, Config.timeout());
    }

    @AfterMethod(alwaysRun = true)
    public void closeBrowser(ITestResult result) {
        if (driver == null) {
            return;
        }

        try {
            if (result.getStatus() == ITestResult.FAILURE) {
                saveScreenshot(result);
            }
        } finally {
            driver.quit();
            driver = null;
        }
    }

    /**
     * Opens a path relative to the configured base URL, e.g. visit("/login").
     */
    protected void visit(String path) {
        driver.get(Config.baseUrl() + path);
    }

    private void saveScreenshot(ITestResult result) {
        try {
            Path directory = Paths.get("target", "screenshots");
            Files.createDirectories(directory);

            String stamp = LocalDateTime.now().format(DateTimeFormatter.ofPattern("yyyyMMdd-HHmmss"));
            Path file = directory.resolve(result.getName() + "-" + stamp + ".png");

            Files.write(file, ((TakesScreenshot) driver).getScreenshotAs(OutputType.BYTES));
            System.out.println("[screenshot] " + file.toAbsolutePath());
        } catch (Exception exception) {
            System.err.println("[screenshot] could not capture: " + exception.getMessage());
        }
    }
}

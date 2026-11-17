package id.pritani.e2e.pages;

import id.pritani.e2e.config.Config;
import id.pritani.e2e.support.XPath;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;

import java.util.List;

public abstract class BasePage {

    protected final WebDriver driver;
    protected final WebDriverWait wait;

    protected BasePage(WebDriver driver) {
        this.driver = driver;
        this.wait = new WebDriverWait(driver, Config.timeout());
    }

    protected void visit(String path) {
        driver.get(Config.baseUrl() + path);
    }

    protected WebElement visible(By locator) {
        return wait.until(ExpectedConditions.visibilityOfElementLocated(locator));
    }

    protected WebElement present(By locator) {
        return wait.until(ExpectedConditions.presenceOfElementLocated(locator));
    }

    protected WebElement clickable(By locator) {
        return wait.until(ExpectedConditions.elementToBeClickable(locator));
    }

    protected void click(By locator) {
        clickable(locator).click();
    }

    protected void type(By locator, String text) {
        WebElement element = visible(locator);
        element.clear();
        element.sendKeys(text);
    }

    protected String text(By locator) {
        return visible(locator).getText().trim();
    }

    protected void selectByText(By locator, String text) {
        new Select(visible(locator)).selectByVisibleText(text);
    }

    protected boolean isVisible(By locator) {
        List<WebElement> elements = driver.findElements(locator);

        return !elements.isEmpty() && elements.get(0).isDisplayed();
    }

    protected void waitUntilGone(By locator) {
        wait.until(ExpectedConditions.invisibilityOfElementLocated(locator));
    }

    protected void waitForPath(String path) {
        wait.until(ExpectedConditions.urlToBe(Config.baseUrl() + path));
    }

    protected void waitForUrlContaining(String fragment) {
        wait.until(ExpectedConditions.urlContains(fragment));
    }

    protected WebElement elementWithText(String text) {
        return visible(textLocator(text));
    }

    protected WebElement tableCell(String text) {
        return visible(tableCellLocator(text));
    }

    protected By textLocator(String text) {
        return By.xpath("//*[normalize-space()=" + XPath.literal(text) + "]");
    }

    protected By tableCellLocator(String text) {
        return By.xpath("//table//tbody//td[normalize-space()=" + XPath.literal(text) + "]");
    }

    public String currentUrl() {
        return driver.getCurrentUrl();
    }

    public String title() {
        return driver.getTitle();
    }
}

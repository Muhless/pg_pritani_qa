package id.pritani.e2e.pages;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;

public class LoginPage extends BasePage {

    private final By HEADER = By.xpath("(//h1[normalize-space()='Masuk'])");
    private final By EMAIL_INPUT = By.id("login_email");
    private final By PASSWORD_INPUT = By.id("login_password");
    private final By SUBMIT_BUTTON = By.cssSelector("button[type='submit']");
    private final By ALERT = By.cssSelector("[role='alert']");

    public LoginPage(WebDriver driver) {
        super(driver);
    }

    public String headerTitle() {
        return text(HEADER);
    }

    public LoginPage goTo() {
        visit("/login");
        visible(HEADER);
        return this;
    }

    private LoginPage fillForm(String email, String password) {
        type(EMAIL_INPUT, email);
        type(PASSWORD_INPUT, password);
        return this;
    }

    public void submit() {
        click(SUBMIT_BUTTON);
    }

    public void login(String email, String password) {
        fillForm(email, password);
        submit();
    }

    private String alertText() {
        return text(ALERT);
    }
}

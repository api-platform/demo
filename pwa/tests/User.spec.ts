import { expect, test } from "./test";

test.describe("User authentication", () => {
  test.beforeEach(async ({ bookPage }) => {
    await bookPage.gotoList();
  });

  test("I can log in @login", async ({ userPage, page }) => {
    await expect(page.getByText("Log in")).toBeVisible();
    await expect(page.getByText("Sign out")).toHaveCount(0);

    await page.getByText("Log in").click();
    await page.getByText("Log in").waitFor({ state: "hidden" });
    // @ts-ignore assert declared on test.ts
    await expect(page).toBeOnLoginPage();
    await expect(page.locator("#kc-header-wrapper")).toContainText("API Platform - Demo");
    await expect(page.locator("#kc-form-login")).toContainText("Login as user: john.doe@example.com");
    await expect(page.locator("#kc-form-login")).toContainText("Login as admin: chuck.norris@example.com");
    await userPage.login();

    await expect(page.getByText("Log in")).toHaveCount(0);
    await expect(page.getByText("Sign out")).toBeVisible();
  });

  test("I can sign out @login", async ({ userPage, page }) => {
    await page.getByText("Log in").click();
    await userPage.login();
    await page.getByText("Sign out").click();

    await expect(page.getByText("Log in")).toBeVisible();
    await expect(page.getByText("Sign out")).toHaveCount(0);

    // I should be logged out from Keycloak also
    await page.getByText("Log in").click();
    // @ts-ignore assert declared on test.ts
    await expect(page).toBeOnLoginPage();
    await expect(page.locator("#kc-header-wrapper")).toContainText("API Platform - Demo");
    await expect(page.locator("#kc-form-login")).toContainText("Login as user: john.doe@example.com");
    await expect(page.locator("#kc-form-login")).toContainText("Login as admin: chuck.norris@example.com");
  });
});

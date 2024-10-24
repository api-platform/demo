import { expect, test } from "./test";

test.describe("User authentication", () => {
  test("I can sign out of Admin @login", async ({ bookPage, page }) => {
    await bookPage.gotoList();

    await page.getByLabel("Profile").click();
    await page.getByRole("menu").getByText("Logout").waitFor({ state: "visible" });
    await page.getByRole("menu").getByText("Logout").click();

    await expect(page).toHaveURL(/\/$/);

    // I should be logged out from Keycloak also
    await page.goto("/admin");
    await page.waitForURL(/\/oidc\/realms\/demo\/protocol\/openid-connect\/auth/);
    // @ts-ignore assert declared on test.ts
    await expect(page).toBeOnLoginPage();
    await expect(page.locator("#kc-header-wrapper")).toContainText("API Platform - Demo");
    await expect(page.locator("#kc-form-login")).toContainText("Login as user: john.doe@example.com");
    await expect(page.locator("#kc-form-login")).toContainText("Login as admin: chuck.norris@example.com");
  });

  test("I see a permission denied error page if I don't have the right permissions @login", async ({ userPage, page }) => {
    await userPage.goToAdminWithInvalidUser();

    await expect(page.locator("#forbidden")).toContainText("You are not allowed to access this page.");
    await page.getByLabel("Profile").click();
    await page.getByRole("menu").getByText("Logout").waitFor({ state: "visible" });
    await page.getByRole("menu").getByText("Logout").click();

    await expect(page).toHaveURL(/\/$/);

    // I should be logged out from Keycloak also
    await page.goto("/admin");
    await page.waitForURL(/\/oidc\/realms\/demo\/protocol\/openid-connect\/auth/);
    // @ts-ignore assert declared on test.ts
    await expect(page).toBeOnLoginPage();
  });
});

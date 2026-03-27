import { expect, test } from "./test";

test.describe("User authentication", () => {
  test.beforeEach(async ({ bookPage }) => {
    await bookPage.gotoList();
  });

  test("I can log in Books Store @login", async ({ userPage, page }) => {
    await expect(page.getByText("Log in")).toBeVisible();
    await expect(page.getByText("Sign out")).toHaveCount(0);

    await page.getByText("Log in").click();
    // Wait for Keycloak login page
    await page.locator('input[value="Log in as user"]').waitFor({ state: "visible", timeout: 30000 });
    // @ts-ignore assert declared on test.ts
    await expect(page).toBeOnLoginPage();
    await expect(page.locator("#kc-header-wrapper")).toContainText("API Platform - Demo");
    await expect(page.locator('input[value="Log in as user"]')).toBeVisible();
    await expect(page.locator('input[value="Log in as admin"]')).toBeVisible();
    await userPage.login();

    await expect(page.getByText("Sign out")).toBeVisible({ timeout: 30000 });
    await expect(page.getByText("Log in")).toHaveCount(0);
  });

  test("I can sign out of Books Store @login", async ({ userPage, page }) => {
    await page.getByText("Log in").click();
    await userPage.login();
    await expect(page.getByText("Sign out")).toBeVisible({ timeout: 30000 });
    await page.getByText("Sign out").click();

    await expect(page.getByText("Log in")).toBeVisible({ timeout: 30000 });
    await expect(page.getByText("Sign out")).toHaveCount(0);

    // I should be logged out from Keycloak also
    await page.getByText("Log in").click();
    await page.locator('input[value="Log in as user"]').waitFor({ state: "visible", timeout: 30000 });
    // @ts-ignore assert declared on test.ts
    await expect(page).toBeOnLoginPage();
    await expect(page.locator("#kc-header-wrapper")).toContainText("API Platform - Demo");
    await expect(page.locator('input[value="Log in as user"]')).toBeVisible();
    await expect(page.locator('input[value="Log in as admin"]')).toBeVisible();
  });
});

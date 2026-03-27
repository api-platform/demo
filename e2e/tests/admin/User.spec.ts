import { expect, test } from "./test";

test.describe("User authentication", () => {
  test.beforeEach(async ({ bookPage }) => {
    await bookPage.gotoList();
  });

  test("I can sign out of Admin @login", async ({ page }) => {
    await page.getByLabel("Profile").click();
    await page.getByRole("menu").getByText("Logout").waitFor({ state: "visible" });
    await page.getByRole("menu").getByText("Logout").click();

    await expect(page).toHaveURL(/\/$/);

    // I should be logged out from Keycloak also
    await page.goto("/admin");
    await page.locator('input[value="Log in as admin"]').waitFor({ state: "visible", timeout: 30000 });
    // @ts-ignore assert declared on test.ts
    await expect(page).toBeOnLoginPage();
    await expect(page.locator("#kc-header-wrapper")).toContainText("API Platform - Demo");
    await expect(page.locator('input[value="Log in as user"]')).toBeVisible();
    await expect(page.locator('input[value="Log in as admin"]')).toBeVisible();
  });
});

test.describe("User permissions", () => {
  test("Non-admin user sees access denied page @login", async ({ userPage, page }) => {
    await userPage.gotoAdminAsUser();

    await expect(page.getByText("Access Denied")).toBeVisible({ timeout: 30000 });
    await expect(page.getByText("You do not have permission to access the administration panel.")).toBeVisible();
    await expect(page.getByRole("link", { name: /browse books/i })).toBeVisible();
    await expect(page.getByRole("button", { name: /sign out/i })).toBeVisible();
  });

  test("Non-admin user can sign out from access denied page @login", async ({ userPage, page }) => {
    await userPage.gotoAdminAsUser();

    await expect(page.getByText("Access Denied")).toBeVisible({ timeout: 30000 });
    await page.getByRole("button", { name: /sign out/i }).click();

    await expect(page).toHaveURL(/\/$/);

    // I should be logged out from Keycloak also
    await page.goto("/admin");
    await page.locator('input[value="Log in as admin"]').waitFor({ state: "visible", timeout: 30000 });
    // @ts-ignore assert declared on test.ts
    await expect(page).toBeOnLoginPage();
  });
});

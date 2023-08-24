import { expect, test } from "./test";

test.describe("User authentication", () => {
  test.beforeEach(async ({ bookPage }) => {
    await bookPage.gotoList();
  });

  test("I can log in @login", async ({ userPage, page }) => {
    await expect(page.getByLabel("Log in")).toBeVisible();
    await expect(page.getByLabel("Sign out")).toHaveCount(0);

    await page.getByLabel("Log in").click();
    // @ts-ignore assert declared on test.ts
    await expect(page).toBeOnLoginPage();
    await userPage.login();

    await expect(page.getByLabel("Log in")).toHaveCount(0);
    await expect(page.getByLabel("Sign out")).toBeVisible();
  });

  test("I can sign out @login", async ({ userPage, page }) => {
    await page.getByLabel("Log in").click();
    await userPage.login();
    await page.getByLabel("Sign out").click();

    await expect(page.getByLabel("Log in")).toBeVisible();
    await expect(page.getByLabel("Sign out")).toHaveCount(0);
  });
});

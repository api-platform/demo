import { expect, test } from "./test";

test.describe("Edit a review @admin", () => {
  test.beforeEach(async ({ reviewPage, page }) => {
    await reviewPage.gotoList();
    await page.locator(".datagrid-body tr").last().getByRole("link", { name: "Edit", exact: true }).click();
  });

  test("I can edit a review @write", async ({ page }) => {
    // await page.waitForTimeout(300);

    // fill in book
    await expect(page.getByLabel("Book")).not.toHaveValue("");
    await page.getByLabel("Book").fill("Hyperion - Dan Simmons");
    await page.getByRole("listbox").getByText("Hyperion - Dan Simmons", { exact: true }).waitFor({ state: "visible" });
    await page.getByRole("listbox").getByText("Hyperion - Dan Simmons", { exact: true }).click();
    await expect(page.getByRole("listbox")).not.toBeAttached();
    await expect(page.getByLabel("Book")).toHaveValue("Hyperion - Dan Simmons");

    // fill in body
    await page.getByLabel("Body").fill("Lorem ipsum dolor sit amet.");

    // fill in rating
    await page.locator(".MuiRating-root label").nth(4).click();

    // submit form
    await page.getByRole("button", { name: "Save", exact: true }).click();
    await expect(page.getByLabel("Book")).not.toBeAttached();
    await expect(page.getByText("Element updated")).toBeVisible();
  });

  test("I can delete a review @write", async ({ page }) => {
    await expect(page.getByRole("button", { name: "Delete" })).toBeVisible();
    await page.getByRole("button", { name: "Delete" }).click();
    await expect(page.getByRole("button", { name: "Confirm" })).toBeVisible();
    await page.getByRole("button", { name: "Confirm" }).click();
    await expect(page.getByLabel("Book")).not.toBeAttached();
    await expect(page.getByText("Element deleted")).toBeVisible();
  });
});

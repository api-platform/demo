import { expect, test } from "./test";

test.describe("Edit a book @admin", () => {
  test.beforeEach(async ({ bookPage, page }) => {
    await bookPage.gotoList();
    await page.locator(".datagrid-body tr").last().getByRole("link", { name: "Edit", exact: true }).click();
  });

  test("I can edit a book @write", async ({ page }) => {
    // fill in Book Reference
    await page.getByLabel("Book Reference").fill("Asimov");
    await page.getByRole("listbox").getByText("The Genetic Effects of Radiation - Asimov, Isaac", { exact: true }).waitFor({ state: "visible" });
    await page.getByRole("listbox").getByText("The Genetic Effects of Radiation - Asimov, Isaac", { exact: true }).click();
    await expect(page.getByRole("listbox")).not.toBeAttached();
    await expect(page.getByLabel("Book Reference")).toHaveValue("The Genetic Effects of Radiation - Asimov, Isaac");

    // fill in condition
    await page.getByLabel("Condition").click();
    await page.getByRole("listbox").getByText("Damaged").waitFor({ state: "visible" });
    await page.getByRole("listbox").getByText("Damaged").click();
    await expect(page.getByRole("listbox")).not.toBeAttached();
    await expect(page.locator(".MuiSelect-nativeInput[name=condition]")).toHaveValue("https://schema.org/DamagedCondition");

    // submit form
    await page.getByRole("button", { name: "Save", exact: true }).click();
    await expect(page.getByLabel("Book Reference")).not.toBeAttached();
    await expect(page.getByText("Element updated")).toBeVisible();
  });

  test("I can delete a book @write", async ({ page }) => {
    await expect(page.getByRole("button", { name: "Delete" })).toBeVisible();
    await page.getByRole("button", { name: "Delete" }).click();
    await expect(page.getByRole("button", { name: "Confirm" })).toBeVisible();
    await page.getByRole("button", { name: "Confirm" }).click();
    await page.getByRole("button", { name: "Confirm" }).waitFor({ state: "detached" });
    await expect(page.getByLabel("Book Reference")).not.toBeAttached();
    await expect(page.getByText("Element deleted")).toBeVisible();
  });
});

import { expect, test } from "./test";

test.describe("Create a book @admin", () => {
  test.beforeEach(async ({ bookPage, page }) => {
    await bookPage.gotoList();
    await page.getByRole("link", { name: "Create", exact: true }).click();
  });

  test("I can create a book @write", async ({ bookPage, page }) => {
    // fill in Book Reference
    await page.getByLabel("Book Reference").fill("Asimov");
    await page.getByRole("listbox").getByText("Let's Get Together - Asimov, Isaac", { exact: true }).waitFor({ state: "visible" });
    await page.getByRole("listbox").getByText("Let's Get Together - Asimov, Isaac", { exact: true }).click();
    await expect(page.getByRole("listbox")).not.toBeAttached();
    await expect(page.getByLabel("Book Reference")).toHaveValue("Let's Get Together - Asimov, Isaac");

    // fill in condition
    await page.getByLabel("Condition").click();
    await page.getByRole("listbox").getByText("Used").waitFor({ state: "visible" });
    await page.getByRole("listbox").getByText("Used").click();
    await expect(page.getByRole("listbox")).not.toBeAttached();
    await expect(page.locator(".MuiSelect-nativeInput[name=condition]")).toHaveValue("https://schema.org/UsedCondition");

    // submit form
    await page.getByRole("button", { name: "Save", exact: true }).click();
    await expect(page.getByLabel("Book Reference")).not.toBeAttached();
    await expect(page.getByText("Element created")).toBeVisible();
  });

  // todo need work in api-platform/core about error handling
  // test("I cannot create a book with an already used Open Library value @read", async ({ bookPage, page }) => {
  //   // fill in Book Reference
  //   await page.getByLabel("Book Reference").fill("Hyperion - Dan Simmons");
  //   await page.getByRole("listbox").getByText("Hyperion - Dan Simmons", { exact: true }).waitFor({ state: "visible" });
  //   await page.getByRole("listbox").getByText("Hyperion - Dan Simmons", { exact: true }).click();
  //   await expect(page.getByRole("listbox")).not.toBeAttached();
  //   await expect(page.getByLabel("Book Reference")).toHaveValue("Hyperion - Dan Simmons");
  //
  //   // fill in condition
  //   await page.getByLabel("Condition").click();
  //   await page.getByRole("listbox").getByText("Used").waitFor({ state: "visible" });
  //   await page.getByRole("listbox").getByText("Used").click();
  //   await expect(page.getByRole("listbox")).not.toBeAttached();
  //   await expect(page.locator(".MuiSelect-nativeInput[name=condition]")).toHaveValue("https://schema.org/UsedCondition");
  //
  //   // submit form
  //   await page.getByRole("button", { name: "Save", exact: true }).click();
  //   await expect(page.getByText("This value is already used.")).toBeVisible();
  // });
});

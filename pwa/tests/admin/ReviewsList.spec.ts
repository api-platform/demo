import { expect, test } from "./test";

const nbItemsPerPage = 10;

test.describe("Admin reviews list @admin", () => {
  test.beforeEach(async ({ reviewPage }) => {
    await reviewPage.gotoList();
  });

  test("I can navigate through the list using the pagination @read", async ({ reviewPage, page }) => {
    // test list display
    await expect(page.locator(".MuiTablePagination-displayedRows")).toContainText(`1-${nbItemsPerPage} of`);
    await expect(page.locator(".datagrid-body tr")).toHaveCount(nbItemsPerPage);
    await expect(page.getByRole("link", { name: "Create", exact: true })).not.toBeAttached();

    // test pagination display
    await expect(page.getByLabel("pagination navigation").locator("li button").first()).toHaveAttribute("aria-label", "Go to previous page");
    await expect(page.getByLabel("pagination navigation").locator("li button").last()).toHaveAttribute("aria-label", "Go to next page");
    await expect(page.getByLabel("pagination navigation").locator("li button.Mui-selected")).toHaveAttribute("aria-label", "Page 1");
    await expect(page.getByLabel("Go to previous page")).toBeDisabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();

    // navigate through pagination
    await page.getByLabel("Go to next page").click();
    await expect(page.locator(".datagrid-body tr")).toHaveCount(nbItemsPerPage);
    await expect(await reviewPage.getDefaultReview()).not.toBeVisible();
    await expect(page.getByLabel("pagination navigation").locator("li button.Mui-selected")).toHaveAttribute("aria-label", "Page 2");
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();

    await page.getByLabel("page 3").click();
    await expect(page.locator(".datagrid-body tr")).toHaveCount(nbItemsPerPage);
    await expect(await reviewPage.getDefaultReview()).not.toBeVisible();
    await expect(page.getByLabel("pagination navigation").locator("li button.Mui-selected")).toHaveAttribute("aria-label", "Page 3");
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();

    await page.getByLabel("Go to previous page").click();
    await expect(page.locator(".datagrid-body tr")).toHaveCount(nbItemsPerPage);
    await expect(await reviewPage.getDefaultReview()).not.toBeVisible();
    await expect(page.getByLabel("pagination navigation").locator("li button.Mui-selected")).toHaveAttribute("aria-label", "Page 2");
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();

    // direct url should target to the right page
    await page.goto("/admin#/admin/reviews?page=2");
    await expect(page.locator(".datagrid-body tr")).toHaveCount(nbItemsPerPage);
    await expect(await reviewPage.getDefaultReview()).not.toBeVisible();
    await expect(page.getByLabel("pagination navigation").locator("li button.Mui-selected")).toHaveAttribute("aria-label", "Page 2");
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
  });

  test("I can filter the list @read", async ({ reviewPage, page }) => {
    // filter by author
    await reviewPage.filter({ user: "John Doe" });
    await expect(page.locator(".MuiTablePagination-displayedRows")).toHaveText("1-1 of 1");
    await expect(page.locator(".datagrid-body tr")).toHaveCount(1);
    await expect(page.getByLabel("pagination navigation")).toHaveCount(0);
    await expect(await reviewPage.getDefaultReview()).toBeVisible();

    // clear filters
    await page.getByLabel("Add filter").click();
    await page.getByRole("menu").getByText("Remove all filters").waitFor({ state: "visible" });
    await page.getByRole("menu").getByText("Remove all filters").click();
    await page.locator(".MuiPopover-root").click(); // close menu
    await expect(page.getByRole("combobox", { name: "User" })).not.toBeVisible();
    await expect(page.locator(".MuiTablePagination-displayedRows")).toContainText(`1-${nbItemsPerPage} of`);
    await expect(page.locator(".datagrid-body tr")).toHaveCount(nbItemsPerPage);

    // filter by user, book and rating
    await reviewPage.filter({ user: "John Doe", book: "Hyperion - Dan Simmons", rating: 5 });
    await expect(page.locator(".MuiTablePagination-displayedRows")).toHaveText("1-1 of 1");
    await expect(page.locator(".datagrid-body tr")).toHaveCount(1);
    await expect(page.getByLabel("pagination navigation")).toHaveCount(0);
    await expect(await reviewPage.getDefaultReview()).toBeVisible();
  });

  test("I can remove a review from the list @write", async ({ page }) => {
    await page.getByRole("checkbox").last().check();
    await expect(page.getByText("1 item selected")).toBeVisible();
    await expect(page.getByRole("button", { name: "Delete", exact: true })).toBeVisible();
    await page.getByRole("button", { name: "Delete", exact: true }).click();
    await expect(page.getByText("Element deleted")).toBeVisible();
  });
});

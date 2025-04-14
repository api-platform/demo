import { expect, test } from "./test";

const nbItemsPerPage = 10;

test.describe("Admin books list @admin", () => {
  test.beforeEach(async ({ bookPage }) => {
    await bookPage.gotoList();
  });

  test("I can navigate through the list using the pagination @read", async ({ bookPage, page }) => {
    // test list display
    await expect(page.locator(".MuiTablePagination-displayedRows")).toContainText(`1-${nbItemsPerPage} of`);
    await expect(page.locator(".datagrid-body tr")).toHaveCount(nbItemsPerPage);

    // test pagination display
    await expect(page.getByLabel("pagination navigation").locator("li button").first()).toHaveAttribute("aria-label", "Go to previous page");
    await expect(page.getByLabel("pagination navigation").locator("li button").last()).toHaveAttribute("aria-label", "Go to next page");
    await expect(page.getByLabel("pagination navigation").locator("li button.Mui-selected")).toHaveAttribute("aria-label", "Page 1");
    await expect(page.getByLabel("Go to previous page")).toBeDisabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();

    // navigate through pagination
    await page.getByLabel("Go to next page").click();
    await expect(page.locator(".datagrid-body tr")).toHaveCount(nbItemsPerPage);
    await expect(await bookPage.getDefaultBook()).not.toBeVisible();
    await expect(page.getByLabel("pagination navigation").locator("li button.Mui-selected")).toHaveAttribute("aria-label", "Page 2");
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();

    await page.getByLabel("page 3").click();
    await expect(page.locator(".datagrid-body tr")).toHaveCount(nbItemsPerPage);
    await expect(await bookPage.getDefaultBook()).not.toBeVisible();
    await expect(page.getByLabel("pagination navigation").locator("li button.Mui-selected")).toHaveAttribute("aria-label", "Page 3");
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();

    await page.getByLabel("Go to previous page").click();
    await expect(page.locator(".datagrid-body tr")).toHaveCount(nbItemsPerPage);
    await expect(await bookPage.getDefaultBook()).not.toBeVisible();
    await expect(page.getByLabel("pagination navigation").locator("li button.Mui-selected")).toHaveAttribute("aria-label", "Page 2");
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();

    // direct url should target to the right page
    await page.goto("/admin#/admin/books?page=2");
    await expect(page.locator(".datagrid-body tr")).toHaveCount(nbItemsPerPage);
    await expect(await bookPage.getDefaultBook()).not.toBeVisible();
    await expect(page.getByLabel("pagination navigation").locator("li button.Mui-selected")).toHaveAttribute("aria-label", "Page 2");
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
  });

  test("I can filter the list @read", async ({ bookPage, page }) => {
    // filter by author
    await bookPage.filter({ author: "Dan Simmons" });
    await expect(page.locator(".MuiTablePagination-displayedRows")).toHaveText("1-1 of 1");
    await expect(page.locator(".datagrid-body tr")).toHaveCount(1);
    await expect(page.getByLabel("pagination navigation")).toHaveCount(0);
    await expect(await bookPage.getDefaultBook()).toBeVisible();

    // clear filters
    await page.getByLabel("Add filter").click();
    await page.getByRole("menu").getByText("Remove all filters").waitFor({ state: "visible" });
    await page.getByRole("menu").getByText("Remove all filters").click();
    await page.locator(".MuiPopover-root").click(); // close menu
    await expect(page.getByLabel("Author")).not.toBeVisible();
    await expect(page.locator(".MuiTablePagination-displayedRows")).toContainText(`1-${nbItemsPerPage} of`);
    await expect(page.locator(".datagrid-body tr")).toHaveCount(nbItemsPerPage);

    // filter by title, author and condition
    await bookPage.filter({ author: "Dan Simmons", title: "Hyperion", condition: "Used" });
    await expect(page.locator(".MuiTablePagination-displayedRows")).toHaveText("1-1 of 1");
    await expect(page.locator(".datagrid-body tr")).toHaveCount(1);
    await expect(page.getByLabel("pagination navigation")).toHaveCount(0);
    await expect(await bookPage.getDefaultBook()).toBeVisible();
  });

  test("I can sort the list by title @read", async ({ bookPage, page }) => {
    await page.getByText("Title").click();
    await expect(await bookPage.getDefaultBook()).not.toBeVisible();
  });

  test("I can get to a book page from the list @read", async ({ bookPage, page }) => {
    const [newPage] = await Promise.all([
      page.context().waitForEvent("page"),
      await (await bookPage.getDefaultBook()).getByRole("link", { name: "Show" }).click(),
    ]);
    await expect(newPage).toHaveURL(/\/books\/.*\/hyperion-dan-simmons$/);
  });

  test("I can remove a book from the list @write", async ({ page }) => {
    await page.getByRole("checkbox").last().check();
    await expect(page.getByText("1 item selected")).toBeVisible();
    await expect(page.getByRole("button", { name: "Delete", exact: true })).toBeVisible();
    await page.getByRole("button", { name: "Delete", exact: true }).click();
    await expect(page.getByText("Element deleted")).toBeVisible();
  });
});

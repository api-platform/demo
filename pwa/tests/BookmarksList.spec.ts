import { expect, test } from "./test";

test.describe("Bookmarks list", () => {
  test.beforeEach(async ({ bookmarkPage, page }) => {
    await bookmarkPage.gotoList();
  });

  test("I can navigate through the list using the pagination @read @login", async ({ bookmarkPage, page }) => {
    // test list display
    await expect(page).toHaveTitle("Bookmarks");
    await expect(page.getByTestId("nb-bookmarks")).toContainText("book(s) bookmarked");
    await expect(page.getByTestId("book").or(page.getByTestId("loading"))).toHaveCount(30);

    // test pagination display
    await expect(page.getByTestId("pagination").locator("li a")).toHaveCount(6);
    await expect(page.getByTestId("pagination").locator("li a").first()).toHaveAttribute("aria-label", "Go to first page");
    await expect(page.getByTestId("pagination").locator("li a").nth(1)).toHaveAttribute("aria-label", "Go to previous page");
    await expect(page.getByTestId("pagination").locator("li a").nth(4)).toHaveAttribute("aria-label", "Go to next page");
    await expect(page.getByTestId("pagination").locator("li a").nth(5)).toHaveAttribute("aria-label", "Go to last page");
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 1");
    await expect(page.getByLabel("Go to first page")).toBeDisabled();
    await expect(page.getByLabel("Go to previous page")).toBeDisabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
    await expect(page.getByLabel("Go to last page")).toBeEnabled();

    // navigate through pagination
    await page.getByLabel("Go to next page").click();
    await expect(page).toHaveURL(/\/bookmarks\?page=2$/);
    await expect(page.getByTestId("book")).toHaveCount(12);
    await expect(await bookmarkPage.getDefaultBook()).not.toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).toBeEnabled();
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeDisabled();
    await expect(page.getByLabel("Go to last page")).toBeDisabled();

    await page.getByLabel("Go to previous page").click();
    await expect(page).toHaveURL(/\/bookmarks\?page=1$/);
    await expect(page.getByTestId("book")).toHaveCount(30);
    await expect(await bookmarkPage.getDefaultBook()).toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 1");
    await expect(page.getByLabel("Go to first page")).toBeDisabled();
    await expect(page.getByLabel("Go to previous page")).toBeDisabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
    await expect(page.getByLabel("Go to last page")).toBeEnabled();

    await page.getByLabel("page 2").click();
    await expect(page).toHaveURL(/\/bookmarks\?page=2$/);
    await expect(page.getByTestId("book")).toHaveCount(12);
    await expect(await bookmarkPage.getDefaultBook()).not.toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).toBeEnabled();
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeDisabled();
    await expect(page.getByLabel("Go to last page")).toBeDisabled();

    await page.getByLabel("Go to previous page").click();
    await expect(page).toHaveURL(/\/bookmarks\?page=1$/);
    await expect(page.getByTestId("book")).toHaveCount(30);
    await expect(await bookmarkPage.getDefaultBook()).toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 1");
    await expect(page.getByLabel("Go to first page")).toBeDisabled();
    await expect(page.getByLabel("Go to previous page")).toBeDisabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
    await expect(page.getByLabel("Go to last page")).toBeEnabled();

    await page.getByLabel("Go to last page").click();
    await expect(page).toHaveURL(/\/bookmarks\?page=2$/);
    await expect(page.getByTestId("book")).toHaveCount(12);
    await expect(await bookmarkPage.getDefaultBook()).not.toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).toBeEnabled();
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeDisabled();
    await expect(page.getByLabel("Go to last page")).toBeDisabled();

    await page.getByLabel("Go to first page").click();
    await expect(page).toHaveURL(/\/bookmarks\?page=1$/);
    await expect(page.getByTestId("book")).toHaveCount(30);
    await expect(await bookmarkPage.getDefaultBook()).toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 1");
    await expect(page.getByLabel("Go to first page")).toBeDisabled();
    await expect(page.getByLabel("Go to previous page")).toBeDisabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
    await expect(page.getByLabel("Go to last page")).toBeEnabled();

    // direct url should target to the right page
    await page.goto("/bookmarks?page=2");
    await page.waitForURL(/\/bookmarks\?page=2$/);
    await expect(page.getByTestId("book")).toHaveCount(12);
    await expect(await bookmarkPage.getDefaultBook()).not.toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).toBeEnabled();
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeDisabled();
    await expect(page.getByLabel("Go to last page")).toBeDisabled();
  });

  test("I can go to the books store filtered by author @read @login", async ({ bookmarkPage, page }) => {
    await (await bookmarkPage.getDefaultBook()).getByText("Isaac Asimov").click();
    await expect(page).toHaveURL(/\/books\?author=Isaac\+Asimov$/);
  });

  test("I can go to a book @read @login", async ({ bookmarkPage, page }) => {
    await (await bookmarkPage.getDefaultBook()).getByText("Foundation").click();
    await expect(page).toHaveURL(/\/books\/.*\/foundation-isaac-asimov$/);
  });
});

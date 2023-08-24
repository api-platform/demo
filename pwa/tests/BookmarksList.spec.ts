import { expect, test } from "./test";

test.describe("Bookmarks list", () => {
  test.beforeEach(async ({ bookmarkPage, page }) => {
    await bookmarkPage.gotoList();
  });

  test("I can navigate through the list using the pagination @read @login", async ({ page }) => {
    // test list display
    await expect(page.title()).toEqual("Bookmarks");
    await expect(page.getByTestId("nb-bookmarks")).toContainText("book(s) bookmarked");
    await expect(page.getByTestId("bookmarks-collection").locator(".relative")).toHaveCount(30);

    // test first element display
    const first = page.getByTestId("bookmarks-collection").locator(".relative").first();
    await expect(first).toHaveText("Foundation");
    await expect(first).toHaveText("Isaac Asimov");

    // test pagination display
    await expect(page.getByTestId("bookmarks-pagination").locator("li")).toHaveCount(8);
    await expect(page.getByTestId("bookmarks-pagination").locator("li").first()).toHaveAttribute("aria-label", "Go to first page");
    await expect(page.getByTestId("bookmarks-pagination").locator("li").nth(1)).toHaveAttribute("aria-label", "Go to previous page");
    await expect(page.getByTestId("bookmarks-pagination").locator("li").nth(6)).toHaveAttribute("aria-label", "Go to next page");
    await expect(page.getByTestId("bookmarks-pagination").locator("li").nth(7)).toHaveAttribute("aria-label", "Go to last page");
    await expect(page.getByTestId("bookmarks-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 1");
    await expect(page.getByLabel("Go to first page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).not.toHaveClass("Mui-disabled");

    // navigate through pagination
    await page.getByLabel("Go to next page").click();
    await expect(page).toHaveURL("https://localhost/bookmarks?page=2");
    await expect(page.getByTestId("bookmarks-collection").locator(".relative")).toHaveCount(8);
    await expect(page.getByTestId("bookmarks-collection").locator(".relative").first()).not.toHaveText("Foundation");
    await expect(page.getByTestId("bookmarks-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).toHaveClass("Mui-disabled");

    await page.getByLabel("Go to previous page").click();
    await expect(page).toHaveURL("https://localhost/bookmarks?page=1");
    await expect(page.getByTestId("bookmarks-collection").locator(".relative")).toHaveCount(30);
    await expect(page.getByTestId("bookmarks-collection").locator(".relative").first()).toHaveText("Foundation");
    await expect(page.getByTestId("bookmarks-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 1");
    await expect(page.getByLabel("Go to first page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).not.toHaveClass("Mui-disabled");

    await page.getByLabel("page 2").click();
    await expect(page).toHaveURL("https://localhost/bookmarks?page=2");
    await expect(page.getByTestId("bookmarks-collection").locator(".relative")).toHaveCount(8);
    await expect(page.getByTestId("bookmarks-collection").locator(".relative").first()).not.toHaveText("Foundation");
    await expect(page.getByTestId("bookmarks-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).toHaveClass("Mui-disabled");

    await page.getByLabel("Go to previous page").click();
    await expect(page).toHaveURL("https://localhost/bookmarks?page=1");
    await expect(page.getByTestId("bookmarks-collection").locator(".relative")).toHaveCount(30);
    await expect(page.getByTestId("bookmarks-collection").locator(".relative").first()).toHaveText("Foundation");
    await expect(page.getByTestId("bookmarks-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 1");
    await expect(page.getByLabel("Go to first page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).not.toHaveClass("Mui-disabled");

    await page.getByLabel("Go to last page").click();
    await expect(page).toHaveURL("https://localhost/bookmarks?page=2");
    await expect(page.getByTestId("bookmarks-collection").locator(".relative")).toHaveCount(8);
    await expect(page.getByTestId("bookmarks-collection").locator(".relative").first()).not.toHaveText("Foundation");
    await expect(page.getByTestId("bookmarks-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).toHaveClass("Mui-disabled");

    await page.getByLabel("Go to first page").click();
    await expect(page).toHaveURL("https://localhost/bookmarks?page=1");
    await expect(page.getByTestId("bookmarks-collection").locator(".relative")).toHaveCount(30);
    await expect(page.getByTestId("bookmarks-collection").locator(".relative").first()).toHaveText("Foundation");
    await expect(page.getByTestId("bookmarks-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 1");
    await expect(page.getByLabel("Go to first page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).not.toHaveClass("Mui-disabled");

    // direct url should target to the right page
    await page.goto("/bookmarks?page=2");
    await page.waitForURL("https://localhost/bookmarks?page=2");
    await expect(page.getByTestId("bookmarks-collection").locator(".relative")).toHaveCount(8);
    await expect(page.getByTestId("bookmarks-collection").locator(".relative").first()).not.toHaveText("Foundation");
    await expect(page.getByTestId("bookmarks-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).toHaveClass("Mui-disabled");
  });

  test("I can go to the books store filtered by author @read @login", async ({ bookPage, page }) => {
    await page.getByText("Isaac Asimov").click();
    await expect(page).toHaveURL("https://localhost/books?author=Isaac+Asimov");
  });

  test("I can go to a book @read @login", async ({ bookPage, page }) => {
    await page.getByText("Foundation").click();
    await expect(page).toHaveURL(/^https:\/\/localhost\/books\/.*\/foundation-isaac-asimov$/);
  });
});

import { expect, test } from "./test";

test.describe("Books list", () => {
  test.beforeEach(async ({ bookPage }) => {
    await bookPage.gotoList();
  });

  test("I can navigate through the list using the pagination @read", async ({ page }) => {
    // test list display
    await expect(page.title()).toEqual("Books Store");
    await expect(page.getByTestId("nb-books")).toHaveText("100 book(s) found");
    await expect(page.getByTestId("books-collection").locator(".relative")).toHaveCount(30);

    // test first element display
    const first = page.getByTestId("books-collection").locator(".relative").first();
    await expect(first).toHaveText("Foundation");
    await expect(first).toHaveText("Isaac Asimov");

    // test pagination display
    await expect(page.getByTestId("books-pagination").locator("li")).toHaveCount(8);
    await expect(page.getByTestId("books-pagination").locator("li").first()).toHaveAttribute("aria-label", "Go to first page");
    await expect(page.getByTestId("books-pagination").locator("li").nth(1)).toHaveAttribute("aria-label", "Go to previous page");
    await expect(page.getByTestId("books-pagination").locator("li").nth(6)).toHaveAttribute("aria-label", "Go to next page");
    await expect(page.getByTestId("books-pagination").locator("li").nth(7)).toHaveAttribute("aria-label", "Go to last page");
    await expect(page.getByTestId("books-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 1");
    await expect(page.getByLabel("Go to first page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).not.toHaveClass("Mui-disabled");

    // navigate through pagination
    await page.getByLabel("Go to next page").click();
    await expect(page).toHaveURL("https://localhost/books?page=2");
    await expect(page.getByTestId("books-collection").locator(".relative")).toHaveCount(30);
    await expect(page.getByTestId("books-collection").locator(".relative").first()).not.toHaveText("Foundation");
    await expect(page.getByTestId("books-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).not.toHaveClass("Mui-disabled");

    await page.getByLabel("page 3").click();
    await expect(page).toHaveURL("https://localhost/books?page=3");
    await expect(page.getByTestId("books-collection").locator(".relative")).toHaveCount(30);
    await expect(page.getByTestId("books-collection").locator(".relative").first()).not.toHaveText("Foundation");
    await expect(page.getByTestId("books-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 3");
    await expect(page.getByLabel("Go to first page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).not.toHaveClass("Mui-disabled");

    await page.getByLabel("Go to previous page").click();
    await expect(page).toHaveURL("https://localhost/books?page=2");
    await expect(page.getByTestId("books-collection").locator(".relative")).toHaveCount(30);
    await expect(page.getByTestId("books-collection").locator(".relative").first()).not.toHaveText("Foundation");
    await expect(page.getByTestId("books-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).not.toHaveClass("Mui-disabled");

    await page.getByLabel("Go to last page").click();
    await expect(page).toHaveURL("https://localhost/books?page=4");
    await expect(page.getByTestId("books-collection").locator(".relative")).toHaveCount(10);
    await expect(page.getByTestId("books-collection").locator(".relative").first()).not.toHaveText("Foundation");
    await expect(page.getByTestId("books-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 4");
    await expect(page.getByLabel("Go to first page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).toHaveClass("Mui-disabled");

    await page.getByLabel("Go to first page").click();
    await expect(page).toHaveURL("https://localhost/books?page=1");
    await expect(page.getByTestId("books-collection").locator(".relative")).toHaveCount(30);
    await expect(page.getByTestId("books-collection").locator(".relative").first()).not.toHaveText("Foundation");
    await expect(page.getByTestId("books-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 1");
    await expect(page.getByLabel("Go to first page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).not.toHaveClass("Mui-disabled");

    // direct url should target to the right page
    await page.goto("/books?page=2");
    await page.waitForURL("https://localhost/books?page=2");
    await expect(page.getByTestId("books-collection").locator(".relative")).toHaveCount(30);
    await expect(page.getByTestId("books-collection").locator(".relative").first()).not.toHaveText("Foundation");
    await expect(page.getByTestId("books-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).not.toHaveClass("Mui-disabled");
  });

  test("I can filter the list @read", async ({ bookPage, page }) => {
    // filter by author
    await bookPage.filter({ author: "Isaac Asimov" });
    await expect(page).toHaveURL("https://localhost/books?author=Isaac+Asimov");
    await expect(page.getByTestId("nb-books")).toHaveText("1 book(s) found");
    await expect(page.getByTestId("books-collection").locator(".relative")).toHaveCount(1);
    await expect(page.getByTestId("books-pagination")).toHaveCount(0);
    const first = page.getByTestId("books-collection").locator(".relative").first();
    await expect(first).toHaveText("Foundation");
    await expect(first).toHaveText("Isaac Asimov");

    // clear author field
    await bookPage.filter({});
    await expect(page.getByTestId("filter-author")).toHaveValue("");
    await expect(page).toHaveURL("https://localhost/books");
    await expect(page.getByTestId("nb-books")).toHaveText("100 book(s) found");
    await expect(page.getByTestId("books-collection").locator(".relative")).toHaveCount(30);

    // filter by title, author and condition
    await bookPage.filter({ author: "Isaac Asimov", title: "Foundation", condition: "Used" });
    await expect(page).toHaveURL("https://localhost/books?author=Isaac+Asimov&title=Foundation&condition%5B%5D=https%3A%2F%2Fschema.org%2FUsedCondition");
    await expect(page.getByTestId("nb-books")).toHaveText("1 book(s) found");
    await expect(page.getByTestId("books-collection").locator(".relative")).toHaveCount(1);
    await expect(page.getByTestId("books-pagination")).toHaveCount(0);
    await expect(first).toHaveText("Foundation");
    await expect(first).toHaveText("Isaac Asimov");

    // click on a book author clears the filters and only apply the author filter
    await page.getByTestId("books-collection").locator(".relative").first().locator("a").nth(1).click();
    await expect(page.getByTestId("filter-author")).toHaveValue("Isaac Asimov");
    await expect(page.getByTestId("filter-title")).toHaveValue("");
    await expect(page.getByTestId("filter-condition-used").isChecked()).toBeFalsy();
    await expect(page).toHaveURL("https://localhost/books?author=Isaac+Asimov");
    await expect(page.getByTestId("nb-books")).toHaveText("1 book(s) found");
    await expect(page.getByTestId("books-collection").locator(".relative")).toHaveCount(1);
    await expect(page.getByTestId("books-pagination")).toHaveCount(0);

    // direct url should apply the filters
    await page.goto("/books?author=Isaac+Asimov&title=Foundation&condition%5B%5D=https%3A%2F%2Fschema.org%2FUsedCondition");
    await expect(page.getByTestId("filter-author")).toHaveValue("Isaac Asimov");
    await expect(page.getByTestId("filter-title")).toHaveValue("Foundation");
    await expect(page.getByTestId("filter-condition-used").isChecked()).toBeTruthy();
    await expect(page.getByTestId("nb-books")).toHaveText("1 book(s) found");
    await expect(page.getByTestId("books-collection").locator(".relative")).toHaveCount(1);
    await expect(page.getByTestId("books-pagination")).toHaveCount(0);
    await expect(first).toHaveText("Foundation");
    await expect(first).toHaveText("Isaac Asimov");
  });

  test("I can sort the list @read", async ({ bookPage, page }) => {
    // sort by title asc
    await bookPage.filter({ order: "Title ASC" });
    await expect(page).toHaveURL("https://localhost/books?order%5Btitle%5D=asc");
    let first = page.getByTestId("books-collection").locator(".relative").first();
    await expect(first).not.toHaveText("Foundation");
    await expect(first).not.toHaveText("Isaac Asimov");

    // sort by title desc
    await bookPage.filter({ order: "Title DESC" });
    await expect(page).toHaveURL("https://localhost/books?order%5Btitle%5D=desc");
    first = page.getByTestId("books-collection").locator(".relative").first();
    await expect(first).not.toHaveText("Foundation");
    await expect(first).not.toHaveText("Isaac Asimov");

    // sort by default (relevance)
    await bookPage.filter({ order: "Relevance" });
    await expect(page).toHaveURL("https://localhost/books");
    first = page.getByTestId("books-collection").locator(".relative").first();
    await expect(first).toHaveText("Foundation");
    await expect(first).toHaveText("Isaac Asimov");

    // direct url should apply the sort
    await page.goto("/books?order%5Btitle%5D=asc")
    await expect(page.getByTestId("sort")).toHaveValue("Title ASC");
    first = page.getByTestId("books-collection").locator(".relative").first();
    await expect(first).not.toHaveText("Foundation");
    await expect(first).not.toHaveText("Isaac Asimov");
  });
});

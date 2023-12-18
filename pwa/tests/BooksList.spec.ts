import { expect, test } from "./test";

const totalBooks = 201;

test.describe("Books list", () => {
  test.beforeEach(async ({ bookPage }) => {
    await bookPage.gotoList();
  });

  test("I can navigate through the list using the pagination @read", async ({ bookPage, page }) => {
    // test list display
    await expect(page).toHaveTitle("Books Store");
    await expect(page.getByTestId("nb-books")).toHaveText(`${totalBooks} book(s) found`);
    await expect(page.getByTestId("book").or(page.getByTestId("loading"))).toHaveCount(30);

    const nbPages = Math.ceil(totalBooks/30);

    // test pagination display
    await expect(page.getByTestId("pagination").locator("li a")).toHaveCount(nbPages+4);
    await expect(page.getByTestId("pagination").locator("li a").first()).toHaveAttribute("aria-label", "Go to first page");
    await expect(page.getByTestId("pagination").locator("li a").nth(1)).toHaveAttribute("aria-label", "Go to previous page");
    await expect(page.getByTestId("pagination").locator("li a").nth(nbPages+2)).toHaveAttribute("aria-label", "Go to next page");
    await expect(page.getByTestId("pagination").locator("li a").nth(nbPages+3)).toHaveAttribute("aria-label", "Go to last page");
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 1");
    await expect(page.getByLabel("Go to first page")).toBeDisabled();
    await expect(page.getByLabel("Go to previous page")).toBeDisabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
    await expect(page.getByLabel("Go to last page")).toBeEnabled();

    // navigate through pagination
    await page.getByLabel("Go to next page").click();
    await expect(page).toHaveURL(/\/books\?page=2$/);
    await expect(page.getByTestId("book").or(page.getByTestId("loading"))).toHaveCount(30);
    await expect(await bookPage.getDefaultBook()).not.toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).toBeEnabled();
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
    await expect(page.getByLabel("Go to last page")).toBeEnabled();

    await page.getByLabel("page 3").click();
    await expect(page).toHaveURL(/\/books\?page=3$/);
    await expect(page.getByTestId("book").or(page.getByTestId("loading"))).toHaveCount(30);
    await expect(await bookPage.getDefaultBook()).not.toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 3");
    await expect(page.getByLabel("Go to first page")).toBeEnabled();
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
    await expect(page.getByLabel("Go to last page")).toBeEnabled();

    await page.getByLabel("Go to previous page").click();
    await expect(page).toHaveURL(/\/books\?page=2$/);
    await expect(page.getByTestId("book").or(page.getByTestId("loading"))).toHaveCount(30);
    await expect(await bookPage.getDefaultBook()).not.toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).toBeEnabled();
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
    await expect(page.getByLabel("Go to last page")).toBeEnabled();

    await page.getByLabel("Go to last page").click();
    await expect(page).toHaveURL(new RegExp(`\/books\\?page=${nbPages}$`));
    await expect(page.getByTestId("book").or(page.getByTestId("loading"))).not.toHaveCount(30);
    await expect(await bookPage.getDefaultBook()).not.toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", `page ${nbPages}`);
    await expect(page.getByLabel("Go to first page")).toBeEnabled();
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeDisabled();
    await expect(page.getByLabel("Go to last page")).toBeDisabled();

    await page.getByLabel("Go to first page").click();
    await expect(page).toHaveURL(/\/books\?page=1$/);
    await expect(page.getByTestId("book").or(page.getByTestId("loading"))).toHaveCount(30);
    await expect(await bookPage.getDefaultBook()).toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 1");
    await expect(page.getByLabel("Go to first page")).toBeDisabled();
    await expect(page.getByLabel("Go to previous page")).toBeDisabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
    await expect(page.getByLabel("Go to last page")).toBeEnabled();

    // direct url should target to the right page
    await page.goto("/books?page=2");
    await page.waitForURL(/\/books\?page=2$/);
    await expect(page.getByTestId("book").or(page.getByTestId("loading"))).toHaveCount(30);
    await expect(await bookPage.getDefaultBook()).not.toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).toBeEnabled();
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
    await expect(page.getByLabel("Go to last page")).toBeEnabled();
  });

  test("I can filter the list @read", async ({ bookPage, page }) => {
    // filter by author
    await bookPage.filter({ author: "Dan Simmons" });
    await expect(page).toHaveURL(/\/books\?author=Dan\+Simmons/);
    await expect(page.getByTestId("nb-books")).toHaveText("1 book(s) found");
    await expect(page.getByTestId("book").or(page.getByTestId("loading"))).toHaveCount(1);
    await expect(page.getByTestId("pagination")).toHaveCount(0);
    await expect(await bookPage.getDefaultBook()).toBeVisible();

    // clear author field
    await page.getByTestId("filter-author").clear();
    await expect(page.getByTestId("filter-author")).toHaveValue("");
    await expect(page).toHaveURL(/\/books$/);
    await expect(page.getByTestId("nb-books")).toHaveText(`${totalBooks} book(s) found`);
    await expect(page.getByTestId("book").or(page.getByTestId("loading"))).toHaveCount(30);

    // filtering must reset the pagination
    await page.getByLabel("Go to next page").click();
    await expect(page).toHaveURL(/\/books\?page=2$/);
    await expect(page.getByTestId("book").or(page.getByTestId("loading"))).toHaveCount(30);
    await expect(await bookPage.getDefaultBook()).not.toBeVisible();
    await bookPage.filter({ author: "Dan Simmons" });
    await expect(page).toHaveURL(/\/books\?author=Dan\+Simmons/);
    await expect(page.getByTestId("nb-books")).toHaveText("1 book(s) found");
    await expect(page.getByTestId("book").or(page.getByTestId("loading"))).toHaveCount(1);
    await expect(page.getByTestId("pagination")).toHaveCount(0);
    await expect(await bookPage.getDefaultBook()).toBeVisible();

    // clear author field
    await page.getByTestId("filter-author").clear();
    await expect(page.getByTestId("filter-author")).toHaveValue("");
    await expect(page).toHaveURL(/\/books$/);
    await expect(page.getByTestId("nb-books")).toHaveText(`${totalBooks} book(s) found`);
    await expect(page.getByTestId("book").or(page.getByTestId("loading"))).toHaveCount(30);

    // filter by title, author and condition
    await bookPage.filter({ author: "Dan Simmons", title: "Hyperion", condition: "Used" });
    await expect(page).toHaveURL(/\/books\?author=Dan\+Simmons&title=Hyperion&condition%5B%5D=https%3A%2F%2Fschema\.org%2FUsedCondition$/);
    await expect(page.getByTestId("nb-books")).toHaveText("1 book(s) found");
    await expect(page.getByTestId("book").or(page.getByTestId("loading"))).toHaveCount(1);
    await expect(page.getByTestId("pagination")).toHaveCount(0);
    await expect(await bookPage.getDefaultBook()).toBeVisible();

    // click on a book author clears the filters and only apply the author filter
    await page.getByTestId("book").first().locator("a").nth(2).click();
    await expect(page.getByTestId("filter-author")).toHaveValue("Dan Simmons");
    await expect(page.getByTestId("filter-title")).toHaveValue("");
    expect(await page.getByTestId("filter-condition-used").isChecked()).toBeFalsy();
    await expect(page).toHaveURL(/\/books\?author=Dan\+Simmons$/);
    await expect(page.getByTestId("nb-books")).toHaveText("1 book(s) found");
    await expect(page.getByTestId("book").or(page.getByTestId("loading"))).toHaveCount(1);
    await expect(page.getByTestId("pagination")).toHaveCount(0);

    // direct url should apply the filters
    await page.goto("/books?author=Dan+Simmons&title=Hyperion&condition%5B%5D=https%3A%2F%2Fschema.org%2FUsedCondition");
    await expect(page.getByTestId("filter-author")).toHaveValue("Dan Simmons");
    await expect(page.getByTestId("filter-title")).toHaveValue("Hyperion");
    expect(await page.getByTestId("filter-condition-used").isChecked()).toBeTruthy();
    await expect(page.getByTestId("nb-books")).toHaveText("1 book(s) found");
    await expect(page.getByTestId("book").or(page.getByTestId("loading"))).toHaveCount(1);
    await expect(page.getByTestId("pagination")).toHaveCount(0);
    await expect(await bookPage.getDefaultBook()).toBeVisible();
  });

  test("I can sort the list @read", async ({ bookPage, page }) => {
    // sort by title asc
    await bookPage.filter({ order: "Title ASC" });
    await expect(page).toHaveURL(/\/books\?order%5Btitle%5D=asc$/);
    await expect(await bookPage.getDefaultBook()).not.toBeVisible()

    // sort by title desc
    await bookPage.filter({ order: "Title DESC" });
    await expect(page).toHaveURL(/\/books\?order%5Btitle%5D=desc$/);
    await expect(await bookPage.getDefaultBook()).not.toBeVisible()

    // sort by default (relevance)
    await bookPage.filter({ order: "Relevance" });
    await expect(page).toHaveURL(/\/books$/);
    await expect(await bookPage.getDefaultBook()).toBeVisible()

    // direct url should apply the sort
    await page.goto("/books?order%5Btitle%5D=asc");
    await expect(page.getByTestId("sort")).toHaveText("Title ASC");
    await expect(await bookPage.getDefaultBook()).not.toBeVisible()
  });
});

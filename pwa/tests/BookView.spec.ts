import { expect, test } from "./test";

test.describe("Book view", () => {
  test.beforeEach(async ({ bookPage }) => {
    await bookPage.gotoDefaultBook();
  });

  test("I can see the book details @read", async ({ page }) => {
    // test book display
    await expect(page.title()).toEqual("Foundation - Isaac Asimov");
    await expect(page.locator("h1")).toHaveText("Foundation");
    await expect(page.locator("h2")).toHaveText("Isaac Asimov");
    await expect(page.getByTestId("book-cover")).toHaveAttribute("src", /covers\.openlibrary\.org\/b\/id\/.*\/-L\.jpg/);
    await expect(page.getByTestId("book-metadata")).toContainText("Condition: Used | Published on 1951");
    await expect(page.getByTestId("book-description")).not.toBeEmpty();
  });

  test("I can go back to the books list through the breadcrumb @read", async ({ page }) => {
    await expect(page.getByTestId("book-breadcrumb")).toContainText("Books Store / Isaac Asimov / Foundation");
    await page.getByTestId("book-breadcrumb").getByText("Books Store").click();
    await expect(page).toHaveURL(/^https:\/\/localhost\/books$/);
  });

  test("I can go back to the books list filtered by author through the breadcrumb @read", async ({ page }) => {
    await expect(page.getByTestId("book-breadcrumb")).toContainText("Books Store / Isaac Asimov / Foundation");
    await page.getByTestId("book-breadcrumb").getByText("Isaac Asimov").click();
    await expect(page).toHaveURL(/^https:\/\/localhost\/books\?author=Isaac\+Asimov$/);
  });

  test("I can bookmark the book @write @login", async ({ bookPage, page }) => {
    // I must log in to bookmark a book
    await bookPage.bookmark();
    // @ts-ignore assert declared on test.ts
    await expect(page).toBeOnLoginPage();
    await bookPage.login();

    await expect(page).toHaveURL(/^https:\/\/localhost\/books\/.*\/foundation-isaac-asimov$/);
    // note: book is already bookmarked through the fixtures
    await expect(page.getByLabel("Bookmark")).toHaveCount(0);
    await expect(page.getByLabel("Bookmarked")).toHaveCount(1);

    await bookPage.unbookmark();
    await expect(page.getByLabel("Bookmark")).toHaveCount(1);
    await expect(page.getByLabel("Bookmarked")).toHaveCount(0);

    await bookPage.bookmark();
    await expect(page.getByLabel("Bookmark")).toHaveCount(0);
    await expect(page.getByLabel("Bookmarked")).toHaveCount(1);
  });

  test("I can navigate through the book reviews @read", async ({ bookPage, page }) => {
    await expect(page.locator("reviews-collection").locator(".mb-5")).toHaveCount(5);

    // test first comment display
    const first = page.locator("reviews-collection").locator(".mb-5").first();
    await expect(first).toContainText("John Doe");
    await expect(first.locator(".MuiRating-root")).toHaveAttribute("aria-label", "5 Stars");

    // test pagination display
    await expect(page.getByTestId("reviews-pagination").locator("li")).toHaveCount(11);
    await expect(page.getByTestId("reviews-pagination").locator("li").first()).toHaveAttribute("aria-label", "Go to first page");
    await expect(page.getByTestId("reviews-pagination").locator("li").nth(1)).toHaveAttribute("aria-label", "Go to previous page");
    await expect(page.getByTestId("reviews-pagination").locator("li").nth(6)).toHaveAttribute("aria-label", "Go to next page");
    await expect(page.getByTestId("reviews-pagination").locator("li").nth(7)).toHaveAttribute("aria-label", "Go to last page");
    await expect(page.getByTestId("reviews-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 1");
    await expect(page.getByLabel("Go to first page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).not.toHaveClass("Mui-disabled");

    // navigate through pagination
    await page.getByLabel("Go to next page").click();
    await expect(page).toHaveURL(/^https:\/\/localhost\/books\/.*\/foundation-isaac-asimov\?page=2#reviews$/);
    await expect(page.getByTestId("reviews-collection").locator(".mb-5")).toHaveCount(5);
    await expect(page.getByTestId("reviews-collection").locator(".mb-5").first()).not.toHaveText("John Doe");
    await expect(page.getByTestId("reviews-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).not.toHaveClass("Mui-disabled");

    await page.getByLabel("page 3").click();
    await expect(page).toHaveURL(/^https:\/\/localhost\/books\/.*\/foundation-isaac-asimov\?page=3#reviews$/);
    await expect(page.getByTestId("reviews-collection").locator(".mb-5")).toHaveCount(5);
    await expect(page.getByTestId("reviews-collection").locator(".mb-5").first()).not.toHaveText("John Doe");
    await expect(page.getByTestId("reviews-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 3");
    await expect(page.getByLabel("Go to first page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).not.toHaveClass("Mui-disabled");

    await page.getByLabel("Go to previous page").click();
    await expect(page).toHaveURL(/^https:\/\/localhost\/books\/.*\/foundation-isaac-asimov\?page=2#reviews$/);
    await expect(page.getByTestId("reviews-collection").locator(".mb-5")).toHaveCount(5);
    await expect(page.getByTestId("reviews-collection").locator(".mb-5").first()).not.toHaveText("John Doe");
    await expect(page.getByTestId("reviews-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).not.toHaveClass("Mui-disabled");

    await page.getByLabel("Go to last page").click();
    await expect(page).toHaveURL(/^https:\/\/localhost\/books\/.*\/foundation-isaac-asimov\?page=7#reviews$/);
    await expect(page.getByTestId("reviews-collection").locator(".mb-5")).toHaveCount(1);
    await expect(page.getByTestId("reviews-collection").locator(".mb-5").first()).not.toHaveText("John Doe");
    await expect(page.getByTestId("reviews-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 4");
    await expect(page.getByLabel("Go to first page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).toHaveClass("Mui-disabled");

    await page.getByLabel("Go to first page").click();
    await expect(page).toHaveURL(/^https:\/\/localhost\/books\/.*\/foundation-isaac-asimov\?page=1#reviews$/);
    await expect(page.getByTestId("reviews-collection").locator(".mb-5")).toHaveCount(5);
    await expect(page.getByTestId("reviews-collection").locator(".mb-5").first()).not.toHaveText("John Doe");
    await expect(page.getByTestId("reviews-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 1");
    await expect(page.getByLabel("Go to first page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).not.toHaveClass("Mui-disabled");

    // direct url should target to the right page
    await bookPage.gotoDefaultBook();
    await page.goto(`${page.url()}?page=2`);
    await page.waitForURL(/^https:\/\/localhost\/books\/.*\/foundation-isaac-asimov\?page=2$/);
    await expect(page.getByTestId("reviews-collection").locator(".mb-5")).toHaveCount(5);
    await expect(page.getByTestId("reviews-collection").locator(".mb-5").first()).not.toHaveText("John Doe");
    await expect(page.getByTestId("reviews-pagination").locator("li .Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to previous page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to next page")).not.toHaveClass("Mui-disabled");
    await expect(page.getByLabel("Go to last page")).not.toHaveClass("Mui-disabled");
  });

  test("I can update my review on a book @write @login", async ({ bookPage, page }) => {
    let first = page.locator("reviews-collection").locator(".mb-5").first();
    await expect(first.getByLabel("Edit")).toHaveCount(0);

    // I must log in to update my review
    await page.getByLabel("Log in to add a review!").click();
    // @ts-ignore assert declared on test.ts
    await expect(page).toBeOnLoginPage();
    await bookPage.login();

    // display edit form
    first = page.locator("reviews-collection").locator(".mb-5").first();
    await expect(first.getByLabel("Edit")).toBeVisible();
    await first.getByLabel("Edit").click();
    await expect(first.getByLabel("Edit")).toHaveCount(0);
    await expect(first.getByTestId("review-body")).toBeVisible();

    // update review
    await bookPage.writeReview({ rating: 4, body: "I really love this book!" }, first);

    // test review display has been updated
    first = page.locator("reviews-collection").locator(".mb-5").first();
    await expect(first).toContainText("John Doe");
    await expect(first).toContainText("I really love this book!");
    await expect(first.locator(".MuiRating-root")).toHaveAttribute("aria-label", "4 Stars");
  });

  test("I can delete my review on a book @write @login", async ({ bookPage, page }) => {
    let first = page.locator("reviews-collection").locator(".mb-5").first();
    await expect(first.getByLabel("Delete")).toHaveCount(0);

    // I must log in to update my review
    await page.getByLabel("Log in to add a review!").click();
    // @ts-ignore assert declared on test.ts
    await expect(page).toBeOnLoginPage();
    await bookPage.login();

    // display edit form
    first = page.locator("reviews-collection").locator(".mb-5").first();
    await expect(first.getByLabel("Delete")).toBeVisible();
    page.on("dialog", dialog => dialog.accept());
    await first.getByLabel("Delete").click();
    await expect(first.getByLabel("Delete")).toHaveCount(0);
    await expect(first.getByTestId("review-body")).toBeVisible();

    // update review
    await bookPage.writeReview({ rating: 4, body: "I really love this book!" }, first);

    // test review display has been updated
    first = page.locator("reviews-collection").locator(".mb-5").first();
    await expect(first).toContainText("John Doe");
    await expect(first).toContainText("I really love this book!");
    await expect(first.locator(".MuiRating-root")).toHaveAttribute("aria-label", "4 Stars");
  });

  // note: this test must be executed after update/delete (cf. previous tests)
  test("I can add a review on a book @write @login", async ({ bookPage, page }) => {
    await expect(page.getByLabel("Log in to add a review!")).toBeVisible();

    // I must log in to review a book
    await page.getByLabel("Log in to add a review!").click();
    // @ts-ignore assert declared on test.ts
    await expect(page).toBeOnLoginPage();
    await bookPage.login();

    await expect(page.getByLabel("Log in to add a review!")).toHaveCount(0);
    await expect(page.getByLabel("Add a review...")).toBeVisible();
    await expect(page.getByLabel("Submit")).toBeVisible();
    await expect(page.getByTestId("review-form")).toContainText("John Doe");

    await bookPage.writeReview({ rating: 5, body: "This is the best SF book ever!" });
    await expect(page.getByLabel("Add a review...")).toHaveValue("");

    // adding a review refresh the list: new review is displayed first
    await expect(page.getByTestId("reviews-collection").locator(".mb-5")).toHaveCount(5);
    const first = page.locator("reviews-collection").locator(".mb-5").first();
    await expect(first).toContainText("John Doe");
    await expect(first).toContainText("This is the best SF book ever!");
    await expect(first.locator(".MuiRating-root")).toHaveAttribute("aria-label", "5 Stars");
  });
});

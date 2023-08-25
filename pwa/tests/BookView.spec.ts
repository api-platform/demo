import { expect, test } from "./test";

test.describe("Book view", () => {
  test.beforeEach(async ({ bookPage }) => {
    await bookPage.gotoDefaultBook();
  });

  test("I can see the book details @read", async ({ page }) => {
    // test book display
    await expect(page).toHaveTitle("Foundation - Isaac Asimov");
    await expect(page.locator("h1")).toHaveText("Foundation");
    await expect(page.locator("h2")).toHaveText("Isaac Asimov");
    await expect(page.getByTestId("book-cover")).toBeVisible();
    await expect(page.getByTestId("book-metadata")).toContainText("Condition: Used | Published on 1951");
    await expect(page.getByTestId("book-description")).not.toBeEmpty();
  });

  test("I can go back to the books list through the breadcrumb @read", async ({ page }) => {
    await expect(page.getByTestId("book-breadcrumb")).toContainText("Books Store");
    await page.getByTestId("book-breadcrumb").getByText("Books Store").click();
    await expect(page).toHaveURL(/\/books$/);
  });

  test("I can go back to the books list filtered by author through the breadcrumb @read", async ({ page }) => {
    await expect(page.getByTestId("book-breadcrumb")).toContainText("Isaac Asimov");
    await page.getByTestId("book-breadcrumb").getByText("Isaac Asimov").click();
    await expect(page).toHaveURL(/\/books\?author=Isaac%20Asimov$/);
  });

  test("I can bookmark the book @write @login", async ({ bookPage, page }) => {
    // I must log in to bookmark a book
    await bookPage.bookmark();
    // @ts-ignore assert declared on test.ts
    await expect(page).toBeOnLoginPage();
    await bookPage.login();

    await expect(page).toHaveURL(/\/books\/.*\/foundation-isaac-asimov$/);
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
    await expect(page.getByTestId("review")).toHaveCount(5);

    // test first comment display
    await expect(await bookPage.getDefaultReview()).toBeVisible();
    await expect(page.getByTestId("review").first().locator(".MuiRating-root")).toHaveAttribute("aria-label", "5 Stars");

    // test pagination display
    await expect(page.getByTestId("pagination").locator("li a")).toHaveCount(11);
    await expect(page.getByTestId("pagination").locator("li a").first()).toHaveAttribute("aria-label", "Go to first page");
    await expect(page.getByTestId("pagination").locator("li a").nth(1)).toHaveAttribute("aria-label", "Go to previous page");
    await expect(page.getByTestId("pagination").locator("li a").nth(9)).toHaveAttribute("aria-label", "Go to next page");
    await expect(page.getByTestId("pagination").locator("li a").nth(10)).toHaveAttribute("aria-label", "Go to last page");
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 1");
    await expect(page.getByLabel("Go to first page")).toBeDisabled();
    await expect(page.getByLabel("Go to previous page")).toBeDisabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
    await expect(page.getByLabel("Go to last page")).toBeEnabled();

    // navigate through pagination
    await page.getByLabel("Go to next page").click();
    await expect(page).toHaveURL(/\/books\/.*\/foundation-isaac-asimov\?page=2#reviews$/);
    await expect(page.getByTestId("review")).toHaveCount(5);
    await expect(await bookPage.getDefaultReview()).not.toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).toBeEnabled();
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
    await expect(page.getByLabel("Go to last page")).toBeEnabled();

    await page.getByLabel("page 3").click();
    await expect(page).toHaveURL(/\/books\/.*\/foundation-isaac-asimov\?page=3#reviews$/);
    await expect(page.getByTestId("review")).toHaveCount(5);
    await expect(await bookPage.getDefaultReview()).not.toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 3");
    await expect(page.getByLabel("Go to first page")).toBeEnabled();
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
    await expect(page.getByLabel("Go to last page")).toBeEnabled();

    await page.getByLabel("Go to previous page").click();
    await expect(page).toHaveURL(/\/books\/.*\/foundation-isaac-asimov\?page=2#reviews$/);
    await expect(page.getByTestId("review")).toHaveCount(5);
    await expect(await bookPage.getDefaultReview()).not.toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).toBeEnabled();
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
    await expect(page.getByLabel("Go to last page")).toBeEnabled();

    await page.getByLabel("Go to last page").click();
    await expect(page).toHaveURL(/\/books\/.*\/foundation-isaac-asimov\?page=7#reviews$/);
    await expect(page.getByTestId("review")).toHaveCount(1);
    await expect(await bookPage.getDefaultReview()).not.toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 7");
    await expect(page.getByLabel("Go to first page")).toBeEnabled();
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeDisabled();
    await expect(page.getByLabel("Go to last page")).toBeDisabled();

    await page.getByLabel("Go to first page").click();
    await expect(page).toHaveURL(/\/books\/.*\/foundation-isaac-asimov\?page=1#reviews$/);
    await expect(page.getByTestId("review")).toHaveCount(5);
    await expect(await bookPage.getDefaultReview()).toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 1");
    await expect(page.getByLabel("Go to first page")).toBeDisabled();
    await expect(page.getByLabel("Go to previous page")).toBeDisabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
    await expect(page.getByLabel("Go to last page")).toBeEnabled();

    // direct url should target to the right page
    await bookPage.gotoDefaultBook();
    await page.goto(`${page.url()}?page=2`);
    await page.waitForURL(/\/books\/.*\/foundation-isaac-asimov\?page=2$/);
    await expect(page.getByTestId("review")).toHaveCount(5);
    await expect(await bookPage.getDefaultReview()).not.toBeVisible();
    await expect(page.getByTestId("pagination").locator("li a.Mui-selected")).toHaveAttribute("aria-label", "page 2");
    await expect(page.getByLabel("Go to first page")).toBeEnabled();
    await expect(page.getByLabel("Go to previous page")).toBeEnabled();
    await expect(page.getByLabel("Go to next page")).toBeEnabled();
    await expect(page.getByLabel("Go to last page")).toBeEnabled();
  });

  test("I can update my review on a book @write @login", async ({ bookPage, page }) => {
    await expect(page.getByTestId("review").first().getByLabel("Edit")).toHaveCount(0);

    // I must log in to update my review
    await page.getByLabel("Log in to add a review!").click();
    // @ts-ignore assert declared on test.ts
    await expect(page).toBeOnLoginPage();
    await bookPage.login();

    // display edit form
    await expect(page.getByTestId("review").first().getByLabel("Edit")).toBeVisible();
    await page.getByTestId("review").first().getByLabel("Edit").click();
    await expect(page.getByTestId("review").first().getByLabel("Edit")).toHaveCount(0);
    await expect(page.getByTestId("review").first().getByTestId("review-body")).toBeVisible();

    // update review
    await bookPage.writeReview({ rating: 4, body: "I really love this book!" }, page.getByTestId("review").first());

    // test review display has been updated
    await expect(page.getByTestId("review").first()).toContainText("John Doe");
    await expect(page.getByTestId("review").first()).toContainText("I really love this book!");
    await expect(page.getByTestId("review").first().locator(".MuiRating-root")).toHaveAttribute("aria-label", "4 Stars");
  });

  test("I can delete my review on a book @write @login", async ({ bookPage, page }) => {
    await expect(page.getByTestId("review").first().getByLabel("Delete")).toHaveCount(0);

    // I must log in to update my review
    await page.getByLabel("Log in to add a review!").click();
    // @ts-ignore assert declared on test.ts
    await expect(page).toBeOnLoginPage();
    await bookPage.login();

    // display edit form
    await expect(page.getByTestId("review").first().getByLabel("Delete")).toBeVisible();
    page.on("dialog", dialog => dialog.accept());
    await page.getByTestId("review").first().getByLabel("Delete").click();
    await expect(page.getByTestId("review").first().getByLabel("Delete")).toHaveCount(0);
    await expect(page.getByTestId("review").first().getByTestId("review-body")).toBeVisible();

    // update review
    await bookPage.writeReview({ rating: 4, body: "I really love this book!" }, page.getByTestId("review").first());

    // test review display has been updated
    await expect(page.getByTestId("review").first()).toContainText("John Doe");
    await expect(page.getByTestId("review").first()).toContainText("I really love this book!");
    await expect(page.getByTestId("review").first().locator(".MuiRating-root")).toHaveAttribute("aria-label", "4 Stars");
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
    await expect(page.getByTestId("review")).toHaveCount(5);
    await expect(page.getByTestId("review").first()).toContainText("John Doe");
    await expect(page.getByTestId("review").first()).toContainText("This is the best SF book ever!");
    await expect(page.getByTestId("review").first().locator(".MuiRating-root")).toHaveAttribute("aria-label", "5 Stars");
  });
});

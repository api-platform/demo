import { Locator } from "@playwright/test";
import { type FiltersProps } from "@/utils/book";
import { AbstractPage } from "./AbstractPage";

interface ReviewProps {
  rating: number;
  body: string;
}

export class BookPage extends AbstractPage {
  public async filter(filters: FiltersProps) {
    await this.page.getByTestId("filter-author").fill(filters.author ?? "");
    await this.page.getByTestId("filter-title").fill(filters.title ?? "");
    await this.page.getByTestId("sort").selectOption(filters.order ?? "Relevance");

    if (typeof filters.condition === "string") {
      await this.page.getByLabel(filters.condition).check();
    } else if (Array.isArray(filters.condition)) {
      for (const condition of filters.condition) {
        await this.page.getByLabel(condition).check();
      }
    }

    return this.page;
  }

  public async writeReview(values: ReviewProps, locator: Locator | undefined = undefined) {
    await (locator ?? this.page).getByTestId("review-body").fill(values.body);
    await (locator ?? this.page).getByTestId("review-rating").locator("label").nth(values.rating).click();
    await (locator ?? this.page).getByLabel("Submit").click();

    return this.page;
  }

  public async gotoList() {
    await this.page.goto("/books");
    await this.page.waitForURL("https://localhost/books");

    return this.page;
  }

  public async gotoDefaultBook() {
    await this.gotoList();
    await this.page.getByTestId("books-collection").locator(".relative").first().locator("a").first().click();
    await this.page.waitForURL(/^https:\/\/localhost\/books\/.*\/foundation-isaac-asimov$/);

    return this.page;
  }

  public async bookmark() {
    await this.page.getByLabel("Bookmark").click();

    return this.page;
  }

  public async unbookmark() {
    await this.page.getByLabel("Bookmarked").click();

    return this.page;
  }
}

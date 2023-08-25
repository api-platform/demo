import { Locator } from "@playwright/test";
import { type FiltersProps } from "@/utils/book";
import { AbstractPage } from "./AbstractPage";

interface ReviewProps {
  rating: number;
  body: string;
}

export class BookPage extends AbstractPage {
  public async filter(filters: FiltersProps) {
    if (filters.author) {
      await this.page.getByTestId("filter-author").fill(filters.author);
    }

    if (filters.title) {
      await this.page.getByTestId("filter-title").fill(filters.title);
    }

    if (filters.order) {
      await this.page.getByTestId("sort").click();
      await this.page.getByText(filters.order).waitFor({state: "visible"});
      await this.page.getByText(filters.order).click();
    }

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

  public async gotoList(url: string | undefined = "/books") {
    await this.page.goto(url);
    await this.page.waitForURL(/\/books/);
    await this.page.waitForResponse("https://openlibrary.org/books/OL6095440M.json");
    await this.page.waitForResponse(/covers\.openlibrary\.org/);
    await (await this.getDefaultBook()).waitFor({ state: "visible" });

    return this.page;
  }

  public async gotoDefaultBook() {
    await this.gotoList("/books?title=Foundation&author=Isaac+Asimov");
    await (await this.getDefaultBook()).getByText("Foundation").first().click();
    await this.page.waitForURL(/\/books\/.*\/foundation-isaac-asimov$/);

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

  public async getDefaultReview() {
    return this.page.getByTestId("review").filter({ hasText: "John Doe" }).first();
  }
}

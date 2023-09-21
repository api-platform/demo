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
      await this.page.getByText(filters.order).waitFor({ state: "visible" });
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
    await (locator ?? this.page).getByTestId("review-rating").locator("label").nth(values.rating-1).click();
    await (locator ?? this.page).getByRole("button", { name: "Submit" }).click();

    return this.page;
  }

  public async gotoList(filters: URLSearchParams | undefined = undefined) {
    await this.registerMock();

    await this.page.goto(`/books${filters && filters.size > 0 ? `?${filters.toString()}` : ""}`);
    await this.page.waitForURL(/\/books/);
    await this.waitForDefaultBookToBeLoaded();

    return this.page;
  }

  public async gotoDefaultBook() {
    await this.gotoList(new URLSearchParams("title=Hyperion&author=Dan+Simmons"));
    await (await this.getDefaultBook()).getByText("Hyperion").first().click();
    await this.page.waitForURL(/\/books\/.*\/hyperion-dan-simmons$/);

    return this.page;
  }

  public async bookmark() {
    await this.page.getByRole("button", { name: "Bookmark" }).click();

    return this.page;
  }

  public async unbookmark() {
    await this.page.getByRole("button", { name: "Bookmarked" }).click();

    return this.page;
  }

  public async getDefaultReview() {
    return this.page.getByTestId("review").filter({ hasText: "John Doe" }).first();
  }
}

import { Page } from "@playwright/test";

export abstract class AbstractPage {
  constructor(protected readonly page: Page) {
  }

  public async login() {
    await this.page.getByLabel("Username or email").fill("john.doe@example.com");
    await this.page.getByLabel("Password").fill("Pa55w0rd");
    await this.page.getByRole("button", { name: "Sign In" }).click();

    return this.page;
  }

  public async getDefaultBook() {
    return this.page.getByTestId("book").filter({ hasText: "The Three-Body Problem" }).filter({ hasText: "Liu Cixin" }).first();
  }

  public async waitForDefaultBookToBeLoaded() {
    await this.page.waitForResponse("https://openlibrary.org/books/OL25840917M.json");
    await this.page.waitForResponse(/9157544-M\.jpg/);
    await (await this.getDefaultBook()).waitFor({ state: "visible" });

    return this.page;
  }

  protected async registerMock() {
    await this.page.route("https://openlibrary.org/books/OL25840917M.json", (route) => route.fulfill({
      path: "tests/mocks/openlibrary.org/books/OL25840917M.json"
    }));
    await this.page.route(/9157544-M\.jpg/, (route) => route.fulfill({
      path: "tests/mocks/covers.openlibrary.org/b/id/9157544-M.jpg",
    }));
  }
}

import { Page } from "@playwright/test";

export abstract class AbstractPage {
  constructor(protected readonly page: Page) {
  }

  public async login() {
    await this.page.getByLabel("Username or email").fill("john.doe@example.com");
    await this.page.getByLabel("Password").fill("Pa55w0rd");
    await this.page.getByRole("button", { name: "Sign In" }).click();
    if (await this.page.getByRole("button", { name: "Sign in with Keycloak" }).count()) {
      await this.page.getByRole("button", { name: "Sign in with Keycloak" }).click();
    }

    return this.page;
  }

  public async getDefaultBook() {
    return this.page.getByTestId("book").filter({ hasText: "Hyperion" }).filter({ hasText: "Dan Simmons" }).first();
  }

  public async waitForDefaultBookToBeLoaded() {
    await this.page.waitForResponse("https://openlibrary.org/books/OL2055137M.json");
    await this.page.waitForResponse(/4066031-M\.jpg/);
    await (await this.getDefaultBook()).waitFor({ state: "visible" });

    return this.page;
  }

  protected async registerMock() {
    await this.page.route("https://openlibrary.org/books/OL2055137M.json", (route) => route.fulfill({
      path: "tests/mocks/openlibrary.org/books/OL2055137M.json"
    }));
    await this.page.route("https://openlibrary.org/works/OL2055137M.json", (route) => route.fulfill({
      path: "tests/mocks/openlibrary.org/books/OL2055137M.json"
    }));
    await this.page.route(/4066031-M\.jpg/, (route) => route.fulfill({
      path: "tests/mocks/covers.openlibrary.org/b/id/4066031-M.jpg",
    }));
  }
}

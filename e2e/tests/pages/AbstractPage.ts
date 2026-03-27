import { Page } from "@playwright/test";

export abstract class AbstractPage {
  constructor(protected readonly page: Page) {
  }

  public async login() {
    // Wait for Keycloak login page (better-auth redirects client-side via JS)
    await this.page.waitForURL(/\/oidc\/realms\/demo\/protocol\/openid-connect\/auth/, { timeout: 30000 });
    await this.page.getByLabel("Email").fill("john.doe@example.com");
    await this.page.getByRole("textbox", { name: "Password" }).fill("Pa55w0rd");
    await this.page.getByRole("button", { name: "Sign In" }).click();

    return this.page;
  }

  public async getDefaultBook() {
    return this.page.getByTestId("book").filter({ hasText: "Hyperion" }).filter({ hasText: "Dan Simmons" }).first();
  }

  public async waitForDefaultBookToBeLoaded() {
    await (await this.getDefaultBook()).waitFor({ state: "visible" });

    return this.page;
  }

  protected async registerMock() {
    await this.page.route(/^https:\/\/openlibrary\.org\/books\/(.+)\.json$/, (route) => route.fulfill({
      path: "tests/mocks/openlibrary.org/books/OL2055137M.json"
    }));
    await this.page.route(/^https:\/\/openlibrary\.org\/works\/(.+)\.json$/, (route) => route.fulfill({
      path: "tests/mocks/openlibrary.org/works/OL1963268W.json"
    }));
    await this.page.route("https://openlibrary.org/search.json?q=Foundation%20Isaac%20Asimov&limit=10", (route) => route.fulfill({
      path: "tests/mocks/openlibrary.org/search/Foundation-Isaac-Asimov.json"
    }));
    await this.page.route("https://openlibrary.org/search.json?q=Eon%20Greg%20Bear&limit=10", (route) => route.fulfill({
      path: "tests/mocks/openlibrary.org/search/Eon-Greg-Bear.json"
    }));
    await this.page.route("https://openlibrary.org/search.json?q=Hyperion%20Dan%20Simmons&limit=10", (route) => route.fulfill({
      path: "tests/mocks/openlibrary.org/search/Hyperion-Dan-Simmons.json"
    }));
    await this.page.route(/^https:\/\/covers\.openlibrary.org\/b\/id\/(.+)\.jpg$/, (route) => route.fulfill({
      path: "tests/mocks/covers.openlibrary.org/b/id/4066031-M.jpg",
    }));
  }
}

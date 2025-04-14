import { Page } from "@playwright/test";

export abstract class AbstractPage {
  constructor(protected readonly page: Page) {
  }

  public async login() {
    await this.page.getByLabel("Email").fill("chuck.norris@example.com");
    await this.page.getByLabel("Password").fill("Pa55w0rd");
    await this.page.getByRole("button", { name: "Sign In" }).click();
    if (await this.page.getByRole("button", { name: "Sign in with Keycloak" }).count()) {
      await this.page.getByRole("button", { name: "Sign in with Keycloak" }).click();
    }

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

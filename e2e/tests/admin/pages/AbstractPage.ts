import { Page } from "@playwright/test";

export abstract class AbstractPage {
  constructor(protected readonly page: Page) {
  }

  public async login() {
    // Wait for Keycloak login page (better-auth redirects client-side via JS)
    await this.page.waitForURL(/\/oidc\/realms\/demo\/protocol\/openid-connect\/auth/, { timeout: 30000 });
    await this.page.getByRole("button", { name: "Log in as admin" }).click();

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
    // Gutendex mocks — always return the same search results (MUI Autocomplete
    // triggers additional searches with the full selected label as query)
    await this.page.route(/^https:\/\/gutendex\.com\/books\?search=/, (route) => {
      return route.fulfill({
        path: "tests/mocks/gutendex.com/search/Asimov.json",
      });
    });
    await this.page.route(/^https:\/\/gutendex\.com\/books\/(\d+)\.json$/, (route) => {
      const match = route.request().url().match(/\/books\/(\d+)\.json/);
      return route.fulfill({
        path: `tests/mocks/gutendex.com/books/${match?.[1]}.json`,
      });
    });
  }
}

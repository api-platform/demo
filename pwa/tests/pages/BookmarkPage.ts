import { AbstractPage } from "./AbstractPage";

export class BookmarkPage extends AbstractPage {
  public async gotoList() {
    await this.page.goto("/books");
    await this.page.getByText("My Bookmarks").click();
    await this.page.getByRole("button", { name: "Sign in with Keycloak" }).click();
    await this.login();
    await this.page.waitForURL(/\/bookmarks$/);
    await this.page.waitForResponse("https://openlibrary.org/books/OL6095440M.json");
    await this.page.waitForResponse(/covers\.openlibrary\.org/);
    await (await this.getDefaultBook()).waitFor({ state: "visible" });

    return this.page;
  }
}

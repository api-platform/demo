import { AbstractPage } from "./AbstractPage";

export class BookmarkPage extends AbstractPage {
  public async gotoList() {
    await this.registerMock();

    await this.page.goto("/books");
    await this.page.getByText("My Bookmarks").click();
    await this.page.getByRole("button", { name: "Sign in with Keycloak" }).click();
    await this.login();
    await this.page.waitForURL(/\/bookmarks$/);
    await this.waitForDefaultBookToBeLoaded();

    return this.page;
  }
}

import { AbstractPage } from "./AbstractPage";

export class BookmarkPage extends AbstractPage {
  public async gotoList() {
    await this.registerMock();

    await this.page.goto("/books");
    await this.page.getByText("Log in").click();
    await this.login();
    await this.page.waitForURL(/\/books/);
    await this.page.getByText("My Bookmarks").click();
    await this.page.waitForURL(/\/bookmarks$/);
    await this.waitForDefaultBookToBeLoaded();

    return this.page;
  }
}

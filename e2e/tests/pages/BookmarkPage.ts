import { AbstractPage } from "./AbstractPage";

export class BookmarkPage extends AbstractPage {
  public async gotoList() {
    await this.registerMock();

    // Navigate to bookmarks — redirects to login if unauthenticated
    await this.page.goto("/bookmarks");
    await this.login();
    await this.page.waitForURL(/\/bookmarks/);
    await this.waitForDefaultBookToBeLoaded();

    return this.page;
  }
}

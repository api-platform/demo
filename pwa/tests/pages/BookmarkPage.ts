import { AbstractPage } from "./AbstractPage";

export class BookmarkPage extends AbstractPage {
    public async gotoList() {
        await this.page.goto("/books");
        await this.page.getByText("My Bookmarks").click();
        await this.page.locator("Sign in with Keycloak").click();
        await this.login();
        await this.page.waitForURL("https://localhost/bookmarks");

        return this.page;
    }
}

import { AbstractPage } from "./AbstractPage";

export class UserPage extends AbstractPage {
  public async gotoAdminAsUser() {
    await this.page.goto("/admin");
    await this.loginAsUser();

    return this.page;
  }
}

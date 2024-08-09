import { AbstractPage } from "./AbstractPage";

export class UserPage extends AbstractPage {
  public async goToAdminWithInvalidUser() {
    await this.registerMock();

    await this.page.goto("/admin");
    await this.loginWithPublicUser();
    await this.page.waitForURL(/\/admin#\/admin/);

    return this.page;
  }

  public async loginWithPublicUser() {
    await this.page.getByLabel("Email").fill("john.doe@example.com");
    await this.page.getByLabel("Password").fill("Pa55w0rd");
    await this.page.getByRole("button", { name: "Sign In" }).click();
    if (await this.page.getByRole("button", { name: "Sign in with Keycloak" }).count()) {
      await this.page.getByRole("button", { name: "Sign in with Keycloak" }).click();
    }

    return this.page;
  }
}

import { Page } from "@playwright/test";

export abstract class AbstractPage {
  constructor(protected readonly page: Page) {
  }

  public async login() {
    await this.page.getByLabel("Username or email").fill("chuck.norris@example.com");
    await this.page.getByLabel("Password").fill("Pa55w0rd");
    await this.page.getByRole("button", { name: "Sign In" }).click();
    if (await this.page.getByRole("button", { name: "Sign in with Keycloak" }).count()) {
      await this.page.getByRole("button", { name: "Sign in with Keycloak" }).click();
    }

    return this.page;
  }
}

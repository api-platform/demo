import { Page } from "@playwright/test";

export abstract class AbstractPage {
  constructor(protected readonly page: Page) {
  }

  public async login() {
    await this.page.getByLabel("Username or email").fill("john.doe@example.com");
    await this.page.getByLabel("Password").fill("Pa55w0rd");
    await this.page.getByLabel("Sign In").click();

    return this.page;
  }
}

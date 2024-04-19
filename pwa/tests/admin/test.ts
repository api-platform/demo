import { Page, test as playwrightTest } from "@playwright/test";

import { expect } from "../test";
import { BookPage } from "./pages/BookPage";
import { ReviewPage } from "./pages/ReviewPage";
import { UserPage } from "./pages/UserPage";

expect.extend({
  toBeOnLoginPage(page: Page) {
    if (page.url().match(/\/oidc\/realms\/demo\/protocol\/openid-connect\/auth/)) {
      return {
        message: () => "passed",
        pass: true,
      };
    }

    return {
      message: () => `toBeOnLoginPage() assertion failed.\nExpected "/oidc/realms/demo/protocol/openid-connect/auth", got "${page.url()}".`,
      pass: false,
    };
  },
});

type Test = {
  bookPage: BookPage,
  reviewPage: ReviewPage,
  userPage: UserPage,
}

export const test = playwrightTest.extend<Test>({
  bookPage: async ({ page }, use) => {
    await use(new BookPage(page));
  },
  reviewPage: async ({ page }, use) => {
    await use(new ReviewPage(page));
  },
  userPage: async ({ page }, use) => {
    await use(new UserPage(page));
  },
});

export { expect };

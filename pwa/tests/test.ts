import { expect, Page, test as playwrightTest } from "@playwright/test";

import { BookPage } from "./pages/BookPage";
import { BookmarkPage } from "./pages/BookmarkPage";
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
  bookmarkPage: BookmarkPage,
  userPage: UserPage,
}

export const test = playwrightTest.extend<Test>({
  bookPage: async ({ page }, use) => {
    await use(new BookPage(page));
  },
  bookmarkPage: async ({ page }, use) => {
    await use(new BookmarkPage(page));
  },
  userPage: async ({ page }, use) => {
    await use(new UserPage(page));
  },
});

export { expect };

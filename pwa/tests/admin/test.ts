import { test as playwrightTest } from "@playwright/test";

import { expect } from "../test";
import { BookPage } from "./pages/BookPage";
import { ReviewPage } from "./pages/ReviewPage";

type Test = {
  bookPage: BookPage,
  reviewPage: ReviewPage,
}

export const test = playwrightTest.extend<Test>({
  bookPage: async ({ page }, use) => {
    await use(new BookPage(page));
  },
  reviewPage: async ({ page }, use) => {
    await use(new ReviewPage(page));
  },
});

export { expect };

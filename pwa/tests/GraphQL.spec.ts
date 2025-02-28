import { expect, test } from "@playwright/test";

test.describe("GraphQL", () => {
  test("Check GraphQL playground @read", async ({ page }) => {
    await page.goto("/graphql");
    await expect(page.getByTestId("graphiql-container")).toContainText("Welcome to GraphiQL");
  });
})

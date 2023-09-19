import { AbstractPage } from "./AbstractPage";

interface FiltersProps {
  user?: string | undefined;
  book?: string | undefined;
  rating?: number | undefined;
}

export class ReviewPage extends AbstractPage {
  public async gotoList() {
    await this.page.goto("/admin");
    await this.login();
    await this.page.waitForURL(/\/admin#\/admin/);
    await this.page.locator(".RaSidebar-fixed").getByText("Reviews").click();

    return this.page;
  }

  public async getDefaultReview() {
    return this.page.locator(".datagrid-body tr").filter({ hasText: "John Doe" });
  }

  public async filter(filters: FiltersProps) {
    if (filters.user) {
      await this.page.getByLabel("Add filter").click();
      await this.page.getByRole("menu").getByText("User").waitFor({ state: "visible" });
      await this.page.getByRole("menu").getByText("User").click();
      await this.page.getByRole("combobox", { name: "User" }).fill(filters.user);
      await this.page.getByRole("listbox").getByText(filters.user, { exact: true }).click();
      await this.page.waitForResponse(/\/reviews/);
    }

    if (filters.book) {
      await this.page.getByLabel("Add filter").click();
      await this.page.getByRole("menu").getByText("Book").waitFor({ state: "visible" });
      await this.page.getByRole("menu").getByText("Book").click();
      await this.page.getByLabel("Book").fill(filters.book);
      await this.page.getByRole("listbox").getByText(filters.book, { exact: true }).click();
      await this.page.waitForResponse(/\/reviews/);
    }

    if (filters.rating) {
      await this.page.getByLabel("Add filter").click();
      await this.page.getByRole("menu").getByText("Rating").waitFor({ state: "visible" });
      await this.page.getByRole("menu").getByText("Rating").click();
      await this.page.locator(".MuiRating-root label").nth(filters.rating-1).click();
      await this.page.waitForResponse(/\/reviews/);
    }

    return this.page;
  }
}

import { AbstractPage } from "./AbstractPage";

interface FiltersProps {
  author?: string | undefined;
  title?: string | undefined;
  condition?: string | undefined;
}

export class BookPage extends AbstractPage {
  public async gotoList() {
    await this.page.goto("/admin");
    await this.login();
    await this.page.waitForURL(/\/admin#\/admin/);
    await this.page.locator(".RaSidebar-fixed").getByText("Books").click();

    return this.page;
  }

  public async getDefaultBook() {
    return this.page.locator(".datagrid-body tr").filter({ hasText: "Hyperion" });
  }

  public async filter(filters: FiltersProps) {
    if (filters.author) {
      await this.page.getByLabel("Add filter").click();
      await this.page.getByRole("menu").getByText("Author").waitFor({ state: "visible" });
      await this.page.getByRole("menu").getByText("Author").click();
      await this.page.getByLabel("Author").fill(filters.author);
      await this.page.waitForResponse(/\/books/);
    }

    if (filters.title) {
      await this.page.getByLabel("Add filter").click();
      await this.page.getByRole("menu").getByText("Title").waitFor({ state: "visible" });
      await this.page.getByRole("menu").getByText("Title").click();
      await this.page.getByLabel("Title").fill(filters.title);
      await this.page.waitForResponse(/\/books/);
    }

    if (filters.condition) {
      await this.page.getByLabel("Add filter").click();
      await this.page.getByRole("menu").getByText("Condition").waitFor({ state: "visible" });
      await this.page.getByRole("menu").getByText("Condition").click();
      await this.page.getByLabel("Condition").click();
      await this.page.getByRole("listbox").getByText(filters.condition).waitFor({ state: "visible" });
      await this.page.getByRole("listbox").getByText(filters.condition).click();
      await this.page.waitForResponse(/\/books/);
    }

    return this.page;
  }
}

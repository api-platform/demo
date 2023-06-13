import {Browser, expect, Page} from "@playwright/test";

export class PageAdmin {
  private page: Page | undefined;

  public constructor(private countElsAllPage: number, private countElsLastPage: number, private url: string) {}
  public async getAdminPage(browser: Browser) {
    this.page = await (await browser.newContext({ignoreHTTPSErrors: true})).newPage()
    await this.page.goto('https://localhost/admin#/login')
    await this.page.waitForTimeout(1500)
    await this.page.getByRole('button', {name: 'SIGN IN'}).click()
    await this.page.waitForTimeout(1500)
    await expect(this.page.url()).toEqual('https://localhost/admin#/books')
    return this.page
  }
  public async getPages(browser: Browser) {
    if(this.page !== undefined)
      return this.page;
    return await this.getAdminPage(browser)
  }
  public async getElsClickable(role: "alert"|"alertdialog"|"application"|"article"|"banner"|"blockquote"|"button"|"caption"|"cell"|"checkbox"|"code"|"columnheader"|"combobox"|"complementary"|"contentinfo"|"definition"|"deletion"|"dialog"|"directory"|"document"|"emphasis"|"feed"|"figure"|"form"|"generic"|"grid"|"gridcell"|"group"|"heading"|"img"|"insertion"|"link"|"list"|"listbox"|"listitem"|"log"|"main"|"marquee"|"math"|"meter"|"menu"|"menubar"|"menuitem"|"menuitemcheckbox"|"menuitemradio"|"navigation"|"none"|"note"|"option"|"paragraph"|"presentation"|"progressbar"|"radio"|"radiogroup"|"region"|"row"|"rowgroup"|"rowheader"|"scrollbar"|"search"|"searchbox"|"separator"|"slider"|"spinbutton"|"status"|"strong"|"subscript"|"superscript"|"switch"|"tab"|"table"|"tablist"|"tabpanel"|"term"|"textbox"|"time"|"timer"|"toolbar"|"tooltip"|"tree"|"treegrid"|"treeitem", name: string) {
    await this.page?.getByRole(role, {name: name, exact: true}).last().click()
    await this.page?.waitForTimeout(1500)
  }
  public async fillData(label: string, data: string) {
    await this.page?.getByLabel(label).fill(data)
  }
  public async CountElsInList() {
    if(this.page !== undefined)
      return (await this.page.locator('tbody >> tr').count()).toString()
  }

  public async goToReviews() {
    await this.getElsClickable('menuitem', 'Reviews')
    await this.page?.waitForTimeout(1500)
    await expect(this.page?.url()).toEqual('https://localhost/admin#/reviews')
  }
  public async goToTopBooks() {
    await this.page?.getByRole('menuitem', { name: 'Top books' }).click()
    await this.page?.waitForTimeout(1500)
    await expect(this.page?.url()).toEqual('https://localhost/admin#/top_books')
  }
  public async changePage(locator: string) {
    await this.page?.getByLabel(locator, {exact: true}).click()
    await this.page?.waitForTimeout(1500)
  }
  public async goToOpenApi() {
    await this.page?.getByRole('button', { name: 'Hydra' }).click()
    await this.page?.waitForTimeout(300)
    await this.page?.locator('ul').last().locator('li').last().click()
    await this.page?.waitForTimeout(300)

  }
}

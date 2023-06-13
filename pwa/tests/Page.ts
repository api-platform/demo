import {Browser, expect, Page} from "@playwright/test";

export class Pages {
  private page: Page | undefined;

  public constructor(private countElsAllPage: number, private countElsLastPage: number, private url: string) {}

  public async getHomePage(browser: Browser) {
    this.page = await (await browser.newContext({ignoreHTTPSErrors: true})).newPage();
    await this.page.goto('https://localhost' + this.url)
    await this.page.waitForTimeout(1500)
    return this.page
  }

  public async changePage(locator: string) {
    await this.page?.getByLabel(locator, {exact: true}).click()
    await this.page?.waitForTimeout(1500)
  }
  public async getPages(browser: Browser) {
    if(this.page !== undefined)
      return this.page;
    return await this.getHomePage(browser)
  }

  public async CountElsInList() {
    if(this.page !== undefined)
      return (await this.page.locator('tbody >> tr').count()).toString()
  }

  public async getElsClickable(role: "alert"|"alertdialog"|"application"|"article"|"banner"|"blockquote"|"button"|"caption"|"cell"|"checkbox"|"code"|"columnheader"|"combobox"|"complementary"|"contentinfo"|"definition"|"deletion"|"dialog"|"directory"|"document"|"emphasis"|"feed"|"figure"|"form"|"generic"|"grid"|"gridcell"|"group"|"heading"|"img"|"insertion"|"link"|"list"|"listbox"|"listitem"|"log"|"main"|"marquee"|"math"|"meter"|"menu"|"menubar"|"menuitem"|"menuitemcheckbox"|"menuitemradio"|"navigation"|"none"|"note"|"option"|"paragraph"|"presentation"|"progressbar"|"radio"|"radiogroup"|"region"|"row"|"rowgroup"|"rowheader"|"scrollbar"|"search"|"searchbox"|"separator"|"slider"|"spinbutton"|"status"|"strong"|"subscript"|"superscript"|"switch"|"tab"|"table"|"tablist"|"tabpanel"|"term"|"textbox"|"time"|"timer"|"toolbar"|"tooltip"|"tree"|"treegrid"|"treeitem", name: string, url: string) {
    await this.page?.getByRole(role, {name: name, exact: true}).last().click()
    await this.page?.waitForURL('https://localhost' + this.url + url)
  }

  public async fillData(label: string, data: string) {
    await this.page?.getByLabel(label).fill(data)
  }
}

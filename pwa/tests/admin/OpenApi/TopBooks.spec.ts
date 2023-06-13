import {expect, test} from "@playwright/test";
import {PageAdmin} from "../PageAdmin";

test("Go to Admin Top Books Open Api", async ({browser}) => {
  const Books = [
    "Go to page 2",
    "Go to page 10",
    "Go to previous page",
    "Go to page 1",
  ]
  const page = new PageAdmin(10,10, "/admin#/top_books")
  await page.getAdminPage(browser)
  await page.goToOpenApi()
  await page.goToTopBooks()
  for (const elts of Books) {
    await page.changePage(elts)
    await expect(await page.CountElsInList()).toEqual("10")
  }
})

test("Go to Admin Top Books Show Open Api", async ({browser}) => {
  const page = new PageAdmin(10,10, "/admin#/top_books")
  await page.getAdminPage(browser)
  await page.goToOpenApi()
  await page.goToTopBooks()
  await page.getElsClickable("link", "Show")
  await expect((await page.getPages(browser)).url()).toContain("/show")
})

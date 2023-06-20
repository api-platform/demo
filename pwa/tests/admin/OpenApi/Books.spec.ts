import {expect, test} from "@playwright/test";
import {PageAdmin} from "../PageAdmin";

const countBooksAllPage = 10
const countBooksLastPage = 1
let reviewsUrl = ''
test('Go to Admin Books Open Api', async ({browser}) => {
  const Books = [
    ['Go to next page', countBooksAllPage.toString()],
    ['Go to page 11', countBooksLastPage.toString()],
    ['Go to previous page', countBooksAllPage.toString()],
    ['Go to page 1', countBooksAllPage.toString()],
  ]
  const page = new PageAdmin(countBooksAllPage, countBooksAllPage, '/admin#/books')
  await page.getAdminPage(browser)
  await page.goToOpenApi()
  for (const elts of Books) {
    await page.getElsClickable('button', elts[0])
    await expect(await page.CountElsInList()).toEqual(elts[1])
  }
})

test('Go to Admin Books Show Open Api', async ({browser}) => {
  const page = new PageAdmin(countBooksAllPage, countBooksAllPage, '/admin#/books')
  await page.getAdminPage(browser)
  await page.goToOpenApi()
  await page.getElsClickable('link', 'Show')
  await expect((await page.getPages(browser)).url()).toContain('/show')
})

test('Go to Admin Books Create Open Api', async ({browser}) => {
  const allLabel = [
    ['isbn', '9783410333852'],
    ['title', 'Recusandae nobis hic rerum delectus dolorum voluptas.'],
    ['description', 'Consequatur aut ullam qui ea. Aut cum vitae nostrum non. Non omnis aut quos ut ad est quidem eum. Voluptates laboriosam ea porro blanditiis eos enim non aut.'],
    ['author', 'Annette Pouros']
  ]
  const page = await new PageAdmin(countBooksAllPage, countBooksLastPage, '/books')
  await page.getAdminPage(browser)
  await page.goToOpenApi()
  await page.getElsClickable('button', 'Go to page 11')
  await page.getPages(browser).then(async (page) => {
    reviewsUrl = await page.locator('tbody >> tr').last().locator('td').nth(6).locator('li').nth(0).innerText()
  })
  await expect(await page.CountElsInList()).toEqual(countBooksLastPage.toString())
  await page.getElsClickable('link', 'Create')
  await page.getPages(browser).then(async (page) => {
    await page.reload()
    await page.waitForTimeout(1500)
    await page.locator('input').nth(4).fill('2023-05-02T15:51')
    await page.getByLabel('Add').click()
    await page.locator('input').last().fill(reviewsUrl.replaceAll('"', ''))
  })
  for (const elts of allLabel) {
    await page.fillData(elts[0], elts[1])
  }
  await page.getElsClickable('button', 'Save')
  await expect(await page.CountElsInList()).toEqual((countBooksLastPage + 1).toString())
})
test('Go to Admin Books Edit Open Api', async ({browser}) => {
  const page = await new PageAdmin(countBooksAllPage, countBooksLastPage, '/books')
  await page.getAdminPage(browser)
  await page.goToOpenApi()
  await page.getElsClickable('button', 'Go to page 11')
  const url = (await (await page.getPages(browser)).locator('tbody >> tr').first().locator('a').last().getAttribute('href')) ?? ""
  await page.getPages(browser).then(async (page) => {
    await page.locator('tbody >> tr').first().getByRole('link', {name: 'Edit'}).click()
  })
  await expect((await page.getPages(browser)).url()).toContain(url)
  await page.getPages(browser).then(async (page) => {
    await page.reload()
    await page.waitForTimeout(1500)
    await page.getByLabel('Add').click()
    await page.locator('input').last().fill(reviewsUrl.replaceAll('"', ''))
  })
  await page.getElsClickable('button', 'Save')
})

test('Go to Admin Books Delete Open Api', async ({browser}) => {
  const page = await new PageAdmin(countBooksAllPage, countBooksLastPage, '/books')
  await page.getAdminPage(browser)
  await page.goToOpenApi()
  await page.getElsClickable('button', 'Go to page 11')
  await expect(await page.CountElsInList()).toEqual((countBooksLastPage + 1).toString())
  await page.getElsClickable('link', 'Edit')
  await page.getElsClickable('button', 'Delete')
  await page.getElsClickable('button', 'Confirm')
  await expect(await page.CountElsInList()).toEqual(countBooksLastPage.toString())
})

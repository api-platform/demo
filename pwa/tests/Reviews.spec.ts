import {expect, test} from '@playwright/test';
import {Pages} from "./Page";

const countReviewsAllPage = 30
const countReviewsLastPage = 21
test('Go to Reviews list', async ({browser}) => {
  const page = await new Pages(countReviewsAllPage, countReviewsLastPage, '/reviews')
  await page.getHomePage(browser)
  await expect((await page.getPages(browser)).url()).toEqual('https://localhost/reviews')
  const Reviews = [
    ['Next page', countReviewsAllPage.toString()],
    ['Last page', countReviewsLastPage.toString()],
    ['Previous page', countReviewsAllPage.toString()],
    ['First page', countReviewsAllPage.toString()],
  ]
  for (const elts of Reviews) {
    await page.changePage(elts[0])
    await expect(await page.CountElsInList()).toEqual(elts[1])
  }
})

test('Go to Reviews Show', async ({browser}) => {
  const page = await new Pages(countReviewsLastPage, countReviewsAllPage, '/reviews')
  await page.getHomePage(browser)
  const url = (await (await page.getPages(browser)).locator('tbody >> tr').last().locator('a').first().textContent())?.replace('/reviews', '') ?? ''
  await page.getElsClickable('link', 'Show', url)
  await expect((await page.getPages(browser)).url()).toEqual('https://localhost/reviews' + url)
})
test('Go to Reviews Edit', async ({browser}) => {
  const page = await new Pages(countReviewsAllPage, countReviewsLastPage, '/reviews')
  await page.getHomePage(browser)
  const url = (await (await page.getPages(browser)).locator('tbody >> tr').last().locator('a').first().textContent())?.replaceAll('/reviews', '') ?? ''
  await page.getElsClickable('link', 'Edit', url + '/edit')
  await expect((await page.getPages(browser)).url()).toContain('/edit')
})

test('Go to Reviews Create', async ({browser}) => {
  const allLabel = [
    ['body', 'Recusandae nobis hic rerum delectus dolorum voluptas.'],
    ['rating', '5'],
    ['author', 'Annette Pouros'],
    ['publicationDate', '2021-05-01T00:00:00+00:00'],
  ]
  const page = await new Pages(countReviewsAllPage, countReviewsLastPage, '/reviews')
  await page.getHomePage(browser)
  await page.changePage('Last page')
  const bookUrl = await (await page.getPages(browser)).locator('tbody >> tr').last().locator('a').nth(1).innerText()
  allLabel.push(['book', bookUrl])
  await expect(await page.CountElsInList()).toEqual(countReviewsLastPage.toString())
  await page.getElsClickable('link', 'Create', '/create')
  for (const elts of allLabel) {
    await page.fillData(elts[0], elts[1])
  }
  await page.getElsClickable('button', 'Submit', '')
  await page.changePage('Last page')
  await expect(await page.CountElsInList()).toEqual((countReviewsLastPage + 1).toString())
})

test('Go to Reviews Delete', async ({browser}) => {
  const page = await new Pages(countReviewsAllPage, countReviewsLastPage, '/reviews')
  await page.getHomePage(browser)
  await page.changePage('Last page')
  await expect(await page.CountElsInList()).toEqual((countReviewsLastPage + 1).toString())
  const url = (await (await page.getPages(browser)).locator('tbody >> tr').last().locator('a').first().textContent())?.replaceAll('/reviews', '') ?? ''
  await page.getElsClickable('link', 'Show', url)
  await (await page.getPages(browser)).on('dialog', async dialog => {
    if (dialog.type() === 'confirm') {
      return dialog.accept()
    }
  })
  await page.getElsClickable('button', 'Delete', '')
  await (await page.getPages(browser)).goto('https://localhost/reviews/page/17')
  await expect(await page.CountElsInList()).toEqual(countReviewsLastPage.toString())
})

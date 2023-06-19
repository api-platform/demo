import {expect, test} from "@playwright/test";
import {PageAdmin} from "../PageAdmin";

const countReviewsAllPage = 10
const countReviewsLastPage = 1

test('Go to Admin Reviews Hydra', async ({browser}) => {
  const Reviews = [
    ['Go to next page', countReviewsAllPage.toString(), 'page=2'],
    ['Go to page 51', countReviewsLastPage.toString(), 'page=51'],
    ['Go to previous page', countReviewsAllPage.toString(), 'page=50'],
    ['Go to page 1', countReviewsAllPage.toString(), 'page=1'],
  ]
  const page = new PageAdmin(countReviewsAllPage, countReviewsLastPage, '/admin#/reviews')
  await page.getAdminPage(browser)
  await page.goToReviews()
  for (const elts of Reviews) {
    await page.getElsClickable('button', elts[0])
    await expect(await page.CountElsInList()).toEqual(elts[1])
  }
})

test('Go to Admin Reviews Show Hydra', async ({browser}) => {
  const page = new PageAdmin(countReviewsAllPage, countReviewsLastPage, '/admin#/reviews')
  await page.getAdminPage(browser)
  await page.goToReviews()
  await page.getElsClickable('link', 'Show')
  await expect((await page.getPages(browser)).url()).toContain('/show')
})

test('Go to Admin Reviews Edit Hydra', async ({browser}) => {
  const page = new PageAdmin(countReviewsAllPage, countReviewsLastPage, '/admin#/reviews')
  await page.getAdminPage(browser)
  await page.goToReviews()
  const url = await page.getPages(browser).then(async (page) => {
    return await page.locator('tbody >> tr').last().getByRole('link', {name: 'Edit'}).getAttribute('href') ?? ""
  })
  await page.getElsClickable('link', 'Edit')
  await expect((await page.getPages(browser)).url()).toEqual('https://localhost/admin' + url)
})

test('Go to Admin Reviews Create Hydra', async ({browser}) => {
  const allLabel = [
    ['body', 'Recusandae nobis hic rerum delectus dolorum voluptas.'],
    ['rating', '5'],
    ['author', 'Annette Pouros']
  ]
  const page = new PageAdmin(countReviewsAllPage, countReviewsLastPage, '/admin#/reviews')
  await page.getAdminPage(browser)
  await page.goToReviews()
  await page.getElsClickable('button', 'Go to page 51')
  await expect(await page.CountElsInList()).toEqual(countReviewsLastPage.toString())
  const bookUrl = await page.getPages(browser).then(async (page) => {
    return await page.locator('tbody >> tr').last().locator('td').nth(3).innerText()
  })
  allLabel.push(['Book', bookUrl])
  await page.getElsClickable('link', 'Create')
  for (const elts of allLabel) {
    await page.fillData(elts[0], elts[1])
  }
  await page.getPages(browser).then(async (page) => {
    await page.locator('input').nth(4).fill('2023-05-02T15:51')
  })
  await page.getElsClickable('button', 'Save')
  await expect(await page.CountElsInList()).toEqual((countReviewsLastPage + 1).toString())
})

test('Go to Admin Reviews Delete Hydra', async ({browser}) => {
  const page = new PageAdmin(countReviewsAllPage, countReviewsLastPage, '/admin#/reviews')
  await page.getAdminPage(browser)
  await page.goToReviews()
  await page.getElsClickable('button', 'Go to page 51')
  await expect(await page.CountElsInList()).toEqual((countReviewsLastPage + 1).toString())
  await page.getElsClickable('link', 'Edit')
  await page.getElsClickable('button', 'Delete')
  await page.getElsClickable('button', 'Confirm')
  await expect(await page.CountElsInList()).toEqual(countReviewsLastPage.toString())
})

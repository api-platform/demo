import {expect, test} from '@playwright/test';
import { Pages } from './Page';

const countBooksAllPage = 30
const countBooksLastPage = 11
test('Go to Books list', async ({ browser }) => {
  const page = await new Pages(countBooksAllPage,countBooksLastPage,'/books')
  await page.getHomePage(browser)
  await expect((await page.getPages(browser)).url()).toEqual('https://localhost/books')
  const Books = [
    ['Next page', countBooksAllPage.toString()],
    ['Last page', countBooksLastPage.toString()],
    ['Previous page', countBooksAllPage.toString()],
    ['First page', countBooksAllPage.toString()],
  ]
  for (const elts of Books) {
    await page.changePage(elts[0])
    await expect(await page.CountElsInList()).toEqual(elts[1])
  }
})

test('Go to Book Show', async ({ browser }) => {
  const page = await new Pages(countBooksAllPage,countBooksLastPage,'/books')
  await page.getHomePage(browser)
  const url = (await (await page.getPages(browser)).locator('tbody >> tr').last().locator('a').first().textContent())?.replace('/books', '') ?? ''
  await page.getElsClickable('link', 'Show', url)
  await expect((await page.getPages(browser)).url()).toEqual('https://localhost/books' + url)
})
test('Go to Book Edit', async ({ browser }) => {
  const page = await new Pages(countBooksAllPage,countBooksLastPage,'/books')
  await page.getHomePage(browser)
    const url = (await (await page.getPages(browser)).locator('tbody >> tr').last().locator('a').first().textContent())?.replaceAll('/books', '') ?? ''
  await page.getElsClickable('link', 'Edit', url+ '/edit')
  await expect((await page.getPages(browser)).url()).toContain('/edit')
})

test('Go to Book Create', async ({ browser }) => {
  const allLabel = [
    ['isbn', '9783410333852'],
    ['title', 'Recusandae nobis hic rerum delectus dolorum voluptas.'],
    ['description', 'Consequatur aut ullam qui ea. Aut cum vitae nostrum non. Non omnis aut quos ut ad est quidem eum. Voluptates laboriosam ea porro blanditiis eos enim non aut.'],
    ['author', 'Annette Pouros'],
    ['publicationDate', '2021-05-01T00:00:00+00:00'],
  ]
  const page = await new Pages(countBooksAllPage,countBooksLastPage,'/books')
  await page.getHomePage(browser)
  await page.changePage('Last page')
  await expect(await page.CountElsInList()).toEqual(countBooksLastPage.toString())
  await page.getElsClickable('link', 'Create', '/create')
  for (const elts of allLabel) {
    await page.fillData(elts[0], elts[1])
  }
  await page.getElsClickable('button', 'Submit', '')
  await page.changePage('Last page')
  await expect(await page.CountElsInList()).toEqual((countBooksLastPage+1).toString())
})

test('Go to Book Delete', async ({ browser }) => {
  const page= await new Pages(countBooksAllPage,countBooksLastPage,'/books')
  await page.getHomePage(browser)
  await page.changePage('Last page')
  await expect(await page.CountElsInList()).toEqual((countBooksLastPage+1).toString())
    const url = (await (await page.getPages(browser)).locator('tbody >> tr').last().locator('a').first().textContent())?.replaceAll('/books', '') ?? ''
  await page.getElsClickable('link', 'Show', url)
  await (await page.getPages(browser)).on('dialog', async dialog => {
    if(dialog.type() === 'confirm') {
      return dialog.accept()
    }
  })
  await page.getElsClickable('button', 'Delete', '')
  await (await page.getPages(browser)).goto('https://localhost/books/page/4')
  await expect(await page.CountElsInList()).toEqual(countBooksLastPage.toString())
})

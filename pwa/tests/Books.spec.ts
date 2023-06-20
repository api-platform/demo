import { expect, test } from "@playwright/test";
import { LoremIpsum } from "lorem-ipsum";

const lorem = new LoremIpsum();

test.describe('Books', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/books')
  })

  test('List books', async ({ page }) => {
    await expect(page).toHaveURL('/books')
    await expect(await page.textContent('h1')).toEqual('Book List')

    // Check pagination
    const isbnSelector = 'table tbody tr:nth-child(1) td:nth-child(2)'
    const pagination = [
      { label: 'Next page', page: 2 },
      { label: 'Last page', page: 4 },
      { label: 'Previous page', page: 3 },
    ]
    for (const pager of pagination) {
      const isbn = await page.textContent(isbnSelector)
      await page.getByLabel(pager.label).click()
      // Ensure url has changed
      await expect(page).toHaveURL(`/books/page/${pager.page}`)
      // Ensure first element of list has changed
      await expect(page.textContent(isbnSelector)).not.toEqual(isbn)
    }
  })

  test('Show a book', async ({ page }) => {
    await expect(page).toHaveURL('/books')

    // Use 7th element to prevent conflict
    await page.getByText('Show').nth(6).click()

    // Ensure url has changed
    await expect(await page.url()).not.toEqual('/books')
    await expect(page.getByText('Edit')).toHaveCount(1)
    await expect(page.getByText('Delete')).toHaveCount(1)
    await expect(page.getByText('Generate')).toHaveCount(1)
    await expect(await page.textContent('h1')).toMatch(/Show Book \/books\/.*/)
  })

  test('Generate a cover', async ({ page }) => {
    await expect(page).toHaveURL('/books')

    // Use 8th element to prevent conflict
    await page.getByText('Show').nth(7).click()
    await expect(page.getByText('Generate')).toHaveCount(1)
    const url = await page.url()

    await page.getByText('Generate').click()
    // Ensure url has not changed
    await expect(await page.url()).toEqual(url)
    // Ensure button has been replaced by an image + a new button
    await expect(page.locator('table tbody tr').last().textContent()).not.toEqual('Generate')
    await expect(page.getByText('Re-generate')).toHaveCount(1)
    await expect(page.getByAltText('Book cover')).toHaveCount(1)
  })

  test('Create a book', async ({ page }) => {
    await expect(page).toHaveURL('/books')

    await page.getByText('Create').click()
    // Ensure url has changed
    await expect(page).toHaveURL('/books/create')

    // Fill in form
    const data = [
      { selector: 'input#book_isbn', value: '9781234567897' },
      { selector: 'input#book_title', value: lorem.generateWords(3) },
      { selector: 'input#book_description', value: lorem.generateSentences(1) },
      { selector: 'input#book_author', value: lorem.generateWords(2) },
      { selector: 'input#book_publicationDate', value: '2023-05-02T15:51' },
    ]
    for (const datum of data) {
      await page.locator(datum.selector).fill(datum.value)
    }

    // Submit form
    await page.getByText('Submit').click()

    // Ensure url has changed
    await expect(page).toHaveURL('/books')
  })

  test('Edit a book', async ({ page }) => {
    await expect(page).toHaveURL('/books')

    // Use 9th element to prevent conflict
    await page.getByText('Edit').nth(8).click()
    // Ensure url has changed
    await expect(page).toHaveURL(/\/books\/.+/)

    // Change title and author
    const title = lorem.generateWords(3)
    const author = lorem.generateWords(2)
    await page.getByLabel('title').fill(title)
    await page.getByLabel('author').fill(author)

    // Submit form
    await page.getByText('Submit').click()

    // Ensure url has changed
    await expect(page).toHaveURL('/books')
  })

  test('Delete a book', async ({ page }) => {
    await page.goto('/books?page=4')

    // Use last element of 4th page to prevent conflict
    await page.getByText('Edit').last().click()
    // Ensure url has changed
    await expect(page).toHaveURL(/\/books\/.+/)

    // https://playwright.dev/docs/dialogs
    page.on('dialog', dialog => dialog.accept());
    await page.getByText('Delete').click()

    // Ensure url has changed
    await expect(page).toHaveURL('/books?page=4')
  })
})

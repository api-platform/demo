import { expect, test } from "@playwright/test";
import { LoremIpsum } from "lorem-ipsum";

const lorem = new LoremIpsum();

test.describe('Reviews', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/reviews')
  })

  test('List reviews', async ({ page }) => {
    await expect(page).toHaveURL('/reviews')
    await expect(await page.textContent('h1')).toEqual('Review List')

    // Check pagination
    const bodySelector = 'table tbody tr:nth-child(1) td:nth-child(2)'
    const pagination = [
      { label: 'Next page', page: 2 },
      { label: 'Last page', page: 4 },
      { label: 'Previous page', page: 3 },
    ]
    for (const pager of pagination) {
      const body = await page.textContent(bodySelector)
      await page.getByLabel(pager.label).click()
      // Ensure url has changed
      await expect(page).toHaveURL(`/reviews/page/${pager.page}`)
      // Ensure first element of list has changed
      await expect(page.textContent(bodySelector)).not.toEqual(body)
    }
  })

  test('Show a review', async ({ page }) => {
    await expect(page).toHaveURL('/reviews')

    // Use 7th element to prevent conflict
    await page.getByText('Show').nth(6).click()

    // Ensure url has changed
    await expect(await page.url()).not.toEqual('/reviews')
    await expect(page.getByText('Edit')).toHaveCount(1)
    await expect(page.getByText('Delete')).toHaveCount(1)
    await expect(await page.textContent('h1')).toMatch(/Show Review \/reviews\/.*/)
  })

  test('Create a review', async ({ page }) => {
    await expect(page).toHaveURL('/reviews')

    // Save book iri
    const bookIri = await page.locator('tbody >> tr').first().locator('a').nth(1).innerText()

    await page.getByText('Create').click()
    // Ensure url has changed
    await expect(page).toHaveURL('/reviews/create')

    // Fill in form
    const data = [
      { selector: 'input#review_body', value: lorem.generateWords(3) },
      { selector: 'input#review_rating', value: '5' },
      { selector: 'input#review_book', value: bookIri },
      { selector: 'input#review_author', value: lorem.generateWords(2) },
      { selector: 'input#review_publicationDate', value: '2023-05-02T15:51' },
    ]
    for (const datum of data) {
      await page.locator(datum.selector).fill(datum.value)
    }

    // Submit form
    await page.getByText('Submit').click()

    // Ensure url has changed
    await expect(page).toHaveURL('/reviews')
  })

  test('Edit a review', async ({ page }) => {
    await expect(page).toHaveURL('/reviews')

    // Use 8th element to prevent conflict
    await page.getByText('Edit').nth(7).click()
    // Ensure url has changed
    await expect(page).toHaveURL(/\/reviews\/.+/)

    // Change title and author
    const body = lorem.generateWords(3)
    const author = lorem.generateWords(2)
    await page.getByLabel('body').fill(body)
    await page.getByLabel('author').fill(author)

    // Submit form
    await page.getByText('Submit').click()

    // Ensure url has changed
    await expect(page).toHaveURL('/reviews')
  })

  test('Delete a review', async ({ page }) => {
    await page.goto('/reviews?page=4')

    // Use last element of 4th page to prevent conflict
    await page.getByText('Edit').last().click()
    // Ensure url has changed
    await expect(page).toHaveURL(/\/reviews\/.+/)

    // https://playwright.dev/docs/dialogs
    page.on('dialog', dialog => dialog.accept());
    await page.getByText('Delete').click()

    // Ensure url has changed
    await expect(page).toHaveURL('/reviews?page=4')
  })
})

import { expect, test } from "@playwright/test";
import { LoremIpsum } from "lorem-ipsum";

const lorem = new LoremIpsum();

test.describe('Books admin with Hydra', () => {
  test.beforeEach(async ({ page }) => {
    // Log in
    await page.goto('/admin#/login')
    await page.getByText('SIGN IN').click()
    await expect(page.locator('input#password')).toHaveCount(0)

    // Switch to OpenAPI
    await page.getByText('Hydra').click()
    await page.getByText('OpenAPI').click()
    await expect(page.getByText('OpenAPI')).toHaveCount(1)
  })

  test('List books', async ({ page }) => {
    await expect(page).toHaveURL('/admin#/books')
    await expect(await page.textContent('div.RaSidebar-fixed a.RaMenuItemLink-active')).toEqual('Books')

    // Check pagination
    const isbnSelector = 'div.list-page table.RaDatagrid-table tbody tr:nth-child(1) td.column-isbn'
    const pageSelector = 'ul.MuiPagination-ul li:nth-child(%d) button'
    const pagination = [
      {label: 'Go to next page', index: 3},
      {label: 'Go to page 11', index: 8},
      {label: 'Go to previous page', index: 7},
    ]
    for (const pager of pagination) {
      await expect(page.locator(pageSelector.replace('%d', pager.index.toString()))).not.toHaveClass(/Mui-selected/)
      const isbn = await page.textContent(isbnSelector)
      await page.getByLabel(pager.label).click()
      // Ensure selected page has changed
      await expect(page.locator(pageSelector.replace('%d', pager.index.toString()))).toHaveClass(/Mui-selected/)
      // Ensure first element of list has changed
      await expect(page.locator(`${isbnSelector}:text("${isbn}]")`)).toHaveCount(0)
    }
  })

  test('Show a book', async ({ page }) => {
    await expect(page).toHaveURL('/admin#/books')

    // Use 4th element to prevent conflict
    await page.getByText('Show').nth(3).click()
    // Ensure url has changed
    await expect(await page.url()).not.toEqual('/admin#/books')
    await expect(page.getByText('Show')).toHaveCount(0)
    await expect(page.getByText('Edit')).toHaveCount(1)
  })

  test.fixme('Create a book ("Add" button doesn\'t work with OpenAPI)', async ({ page }) => {
    await expect(page).toHaveURL('/admin#/books')

    // Save a review iri
    const reviewIri = await page.locator('tbody >> tr').first().locator('td').nth(6).locator('li').nth(0).textContent()

    await page.getByText('Create').click()
    // Ensure url has changed
    await expect(page).toHaveURL('/admin#/books/create')

    // Fill in form
    const data = [
      {selector: 'input#isbn', value: '9783410333852'},
      {selector: 'input#title', value: lorem.generateWords(3)},
      {selector: 'input#description', value: lorem.generateSentences(1)},
      {selector: 'input#author', value: lorem.generateWords(2)},
      {selector: 'input#publicationDate', value: '2023-05-02T15:51'},
    ]
    for (const datum of data) {
      await page.locator(datum.selector).fill(datum.value)
    }
    await page.getByLabel('Add').click()
    await page.locator('input').last().fill(reviewIri.replaceAll('"', ''))

    // Submit form
    await page.getByText('Save').click()

    // Ensure url has changed
    await expect(page).toHaveURL('/admin#/books')
  })

  test('Edit a book', async ({ page }) => {
    await expect(page).toHaveURL('/admin#/books')

    // Use 5th element to prevent conflict
    await page.getByText('Edit').nth(4).click()
    // Ensure url has changed
    await expect(page).toHaveURL(/\/admin#\/books\/%2Fbooks%2F.+/)

    // Change title and author
    const title = lorem.generateWords(3)
    const author = lorem.generateWords(2)
    await page.getByLabel('Title').fill(title)
    await page.getByLabel('Author').fill(author)

    // Submit form
    await page.getByText('Save').click()

    // Ensure url has changed
    await expect(page).toHaveURL('/admin#/books')
  })

  test('Delete a book', async ({ page }) => {
    await page.goto('/admin#/books?page=3')

    // Use 1st element of 3rd page to prevent conflict
    await page.getByText('Edit').first().click()
    // Ensure url has changed
    await expect(page).toHaveURL(/\/admin#\/books\/%2Fbooks%2F.+/)

    await page.getByText('Delete').click()
    // Ensure confirmation popup is shown
    await expect(await page.getByText('Confirm')).toHaveCount(1)

    // Confirm
    await page.getByText('Confirm').click()

    // Ensure url has changed
    await expect(page).toHaveURL('/admin#/books')
    await expect(await page.locator('.MuiSnackbarContent-message')).toHaveCount(1)
    await expect(await page.textContent('.MuiSnackbarContent-message')).toEqual('Element deleted')
  })
})

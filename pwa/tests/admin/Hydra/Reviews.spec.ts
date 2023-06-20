import { expect, test } from "@playwright/test";
import { LoremIpsum } from "lorem-ipsum";

const lorem = new LoremIpsum();

test.describe('Reviews admin with Hydra', () => {
  test.beforeEach(async ({ page }) => {
    // Log in
    await page.goto('/admin#/login')
    await page.getByText('SIGN IN').click()
    await expect(page.locator('input#password')).toHaveCount(0)

    // Ensure we're using Hydra
    await expect(page.getByText('Hydra')).toHaveCount(1)

    // Go to reviews list
    await page.goto('/admin#/reviews')
  })

  test('List reviews', async ({ page }) => {
    await expect(page).toHaveURL('/admin#/reviews')
    await expect(await page.textContent('div.RaSidebar-fixed a.RaMenuItemLink-active')).toEqual('Reviews')

    // Check pagination
    const bodySelector = 'div.list-page table.RaDatagrid-table tbody tr:nth-child(1) td.column-body'
    const pageSelector = 'ul.MuiPagination-ul li:nth-child(%d) button'
    const pagination = [
      { label: 'Go to next page', index: 3 },
      { label: 'Go to page 10', index: 8 },
      { label: 'Go to previous page', index: 7 },
    ]
    for (const pager of pagination) {
      await expect(page.locator(pageSelector.replace('%d', pager.index.toString()))).not.toHaveClass(/Mui-selected/)
      const body = await page.textContent(bodySelector)
      await page.getByLabel(pager.label).click()
      // Ensure selected page has changed
      await expect(page.locator(pageSelector.replace('%d', pager.index.toString()))).toHaveClass(/Mui-selected/)
      // Ensure first element of list has changed
      await expect(page.locator(`${bodySelector}:text("${body}]")`)).toHaveCount(0)
    }
  })

  test('Show a review', async ({ page }) => {
    await expect(page).toHaveURL('/admin#/reviews')

    await page.getByText('Show').first().click()
    // Ensure url has changed
    await expect(await page.url()).not.toEqual('/admin#/reviews')
    await expect(page.getByText('Show')).toHaveCount(0)
    await expect(page.getByText('Edit')).toHaveCount(1)
  })

  test('Create a review', async ({ page }) => {
    await expect(page).toHaveURL('/admin#/reviews')

    const data = [
      { selector: 'input#body', value: lorem.generateSentences(1) },
      { selector: 'input#rating', value: '5' },
      { selector: 'input#author', value: lorem.generateWords(2) },
      { selector: 'input#book', value: await page.locator('tbody >> tr').first().locator('td').nth(3).innerText() },
      { selector: 'input#publicationDate', value: '2023-05-02T15:51' },
    ]

    await page.getByText('Create').click()
    // Ensure url has changed
    await expect(page).toHaveURL('/admin#/reviews/create')

    // Fill in form
    for (const datum of data) {
      await page.locator(datum.selector).fill(datum.value)
    }

    // Submit form
    await page.getByText('Save').click()

    // Ensure url has changed
    await expect(page).toHaveURL('/admin#/reviews')
  })

  test('Edit a review', async ({ page }) => {
    await expect(page).toHaveURL('/admin#/reviews')

    // Use 2nd element to prevent conflict
    await page.getByText('Edit').nth(1).click()
    // Ensure url has changed
    await expect(page).toHaveURL(/\/admin#\/reviews\/%2Freviews%2F.+/)

    // Change title and author
    const body = lorem.generateWords(3)
    const author = lorem.generateWords(2)
    await page.getByLabel('Body').fill(body)
    await page.getByLabel('Author').fill(author)

    // Submit form
    await page.getByText('Save').click()

    // Ensure url has changed
    await expect(page).toHaveURL('/admin#/reviews')
  })

  test('Delete a review', async ({ page }) => {
    await page.goto('/admin#/reviews?page=2')

    // Use 1st element of 2nd page to prevent conflict
    await page.getByText('Edit').first().click()
    // Ensure url has changed
    await expect(page).toHaveURL(/\/admin#\/reviews\/%2Freviews%2F.+/)

    await page.getByText('Delete').click()
    // Ensure confirmation popup is shown
    await expect(await page.getByText('Confirm')).toHaveCount(1)

    // Confirm
    await page.getByText('Confirm').click()

    // Ensure url has changed
    await expect(page).toHaveURL('/admin#/reviews')
    await expect(await page.locator('.MuiSnackbarContent-message')).toHaveCount(1)
    await expect(await page.textContent('.MuiSnackbarContent-message')).toEqual('Element deleted')
  })
})

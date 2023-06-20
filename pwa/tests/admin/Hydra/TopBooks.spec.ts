import { expect, test } from "@playwright/test";

test.describe('TopBooks admin with Hydra', () => {
  test.beforeEach(async ({ page }) => {
    // Log in
    await page.goto('/admin#/login')
    await page.getByText('SIGN IN').click()
    await expect(page.locator('input#password')).toHaveCount(0)

    // Ensure we're using Hydra
    await expect(page.getByText('Hydra')).toHaveCount(1)

    // Go to TopBooks list
    await page.goto('/admin#/top_books')
  })

  test('List TopBooks', async ({ page }) => {
    await expect(page).toHaveURL('/admin#/top_books')
    await expect(await page.textContent('div.RaSidebar-fixed a.RaMenuItemLink-active')).toEqual('Top books')

    // Check pagination
    const titleSelector = 'div.list-page table.RaDatagrid-table tbody tr:nth-child(1) td.column-title'
    const pageSelector = 'ul.MuiPagination-ul li:nth-child(%d) button'
    const pagination = [
      { label: 'Go to next page', index: 3 },
      { label: 'Go to page 10', index: 8 },
      { label: 'Go to previous page', index: 7 },
    ]
    for (const pager of pagination) {
      await expect(page.locator(pageSelector.replace('%d', pager.index.toString()))).not.toHaveClass(/Mui-selected/)
      const title = await page.textContent(titleSelector)
      await page.getByLabel(pager.label).click()
      // Ensure selected page has changed
      await expect(page.locator(pageSelector.replace('%d', pager.index.toString()))).toHaveClass(/Mui-selected/)
      // Ensure first element of list has changed
      await expect(page.locator(`${titleSelector}:text("${title}]")`)).toHaveCount(0)
    }
  })

  test('Show a TopBook', async ({ page }) => {
    await expect(page).toHaveURL('/admin#/top_books')

    await page.getByText('Show').first().click()
    // Ensure url has changed
    await expect(await page.url()).not.toEqual('/admin#/top_books')
    await expect(page.getByText('Show')).toHaveCount(0)
    await expect(page.getByText('Edit')).toHaveCount(0)
  })
})

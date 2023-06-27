import { expect, test } from "@playwright/test";

test.describe('Homepage', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/')
  })

  test('Check homepage', async ({ page }) => {
    await expect(await page.title()).toEqual('Welcome to API Platform!')
  })

  test('Go To les-tilleuls site', async ({ browser, page }) => {
    const [newPage] = await Promise.all([
      page.context().waitForEvent('page'),
      await page.getByRole('link', { name: 'Made with by Les-Tilleuls.coop' }).click()
    ])
    await expect(newPage).toHaveURL('https://les-tilleuls.coop/en')
  })

  test('Go To Next Js', async ({ page }) => {
    const [newPage] = await Promise.all([
      page.context().waitForEvent('page'),
      await page.getByRole('link', { name: 'Next.js' }).click()
    ])
    await expect(newPage).toHaveURL('https://nextjs.org/')
  })

  test('Go To Api platform Docs', async ({ page }) => {
    const [newPage] = await Promise.all([
      page.context().waitForEvent('page'),
      await page.getByRole('link', { name: 'Get started' }).click()
    ])
    await expect(newPage).toHaveURL('https://api-platform.com/docs/')
  })

  test('Go To Twitter', async ({ page }) => {
    const [newPage] = await Promise.all([
      page.context().waitForEvent('page'),
      await page.getByRole('link', { name: 'API Platform on Twitter' }).click()
    ])
    await expect(newPage).toHaveURL('https://twitter.com/ApiPlatform')
  })

  test('Go To Mastodon', async ({ page }) => {
    const [newPage] = await Promise.all([
      page.context().waitForEvent('page'),
      await page.getByRole('link', { name: 'API Platform on Mastodon' }).click()
    ])
    await expect(newPage).toHaveURL('https://fosstodon.org/@ApiPlatform')
  })

  test('Go To Api', async ({ page }) => {
    await page.getByRole('link', { name: 'API', exact: true }).click()
    await expect(page).toHaveURL('https://localhost/docs')
  })

  test('Go To Admin', async ({ page }) => {
    await page.getByRole('link', { name: 'Admin' }).click()
    await expect(page).toHaveURL('https://localhost/admin')
  })

  test('Go To Mercure Debugger', async ({ page }) => {
    await page.getByRole('link', { name: 'Mercure debugger' }).click()
    await expect(page).toHaveURL('https://localhost/.well-known/mercure/ui/')
  })
})

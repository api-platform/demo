import {Browser, BrowserContext, expect, test} from '@playwright/test';

let context: BrowserContext

async function getHomePage(browser: Browser) {
  context = await browser.newContext({ignoreHTTPSErrors: true})
  const page = await context.newPage()
  await page.goto('http://localhost')
  return page
}

test('Go to HomePage', async ({browser}) => {
  const page = await getHomePage(browser)
  expect(await page.title()).toEqual('Welcome to API Platform!')
})
test('Go To les-tilleuls site', async ({browser}) => {
  const page = await getHomePage(browser)
  const [newPage] = await Promise.all([
    context.waitForEvent('page'),
    await page.getByRole('link', {name: 'Made with by Les-Tilleuls.coop'}).click()
  ])
  await expect(await newPage.url()).toEqual('https://les-tilleuls.coop/en')
})
test('Go To Next Js', async ({browser}) => {
  const page = await getHomePage(browser)
  await page.getByRole('link', {name: 'Next.js'}).click()
  await expect(page.url()).toEqual('https://nextjs.org/')
})
test('Go To Api platform Docs', async ({browser}) => {
  const page = await getHomePage(browser)
  const [newPage] = await Promise.all([
    context.waitForEvent('page'),
    await page.getByRole('link', {name: 'Get started'}).click()
  ])
  await expect(await newPage.url()).toEqual('https://api-platform.com/docs/')
})
test('Go To Twitter', async ({browser}) => {
  const page = await getHomePage(browser)
  const [newPage] = await Promise.all([
    context.waitForEvent('page'),
    await page.getByRole('link', {name: 'API Platform on Twitter'}).click()
  ])
  await expect(await newPage.url()).toEqual('https://twitter.com/ApiPlatform')
})

test('Go To Mastodon', async ({browser}) => {
  const page = await getHomePage(browser)
  const [newPage] = await Promise.all([
    context.waitForEvent('page'),
    await page.getByRole('link', {name: 'API Platform on Mastodon'}).click()
  ])
  await expect(await newPage.url()).toEqual('https://fosstodon.org/@ApiPlatform')
})
test('Go To Api', async ({browser}) => {
  const page = await getHomePage(browser)
  await page.getByRole('link', {name: 'API', exact: true}).click()
  await expect(page.url()).toEqual('https://localhost/docs')
})
test('Go To Admin', async ({browser}) => {
  const page = await getHomePage(browser)
  await page.getByRole('link', {name: 'Admin'}).click()
  await expect(page.url()).toEqual('https://localhost/admin')
})
test('Go To Mercure Debugger', async ({browser}) => {
  const page = await getHomePage(browser)
  await page.getByRole('link', {name: 'Mercure debugger'}).click()
  await expect(page.url()).toEqual('https://localhost/.well-known/mercure/ui/')
})

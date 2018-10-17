const { After, AfterAll } = require('cucumber');
const { scope } = require('./support');
const { dashboard } = require('./pages');
const path = require('path');

After(async scenario => {
  const page = scope.context.currentPage;

  if (!page) {
    return;
  }

  if ('failed' === scenario.result.status) {
    await page.screenshot({
      path: path.join(
        __dirname,
        'screenshots',
        `${scenario.pickle.name
          .replace(/[^A-z\d]+/gi, '-')
          .replace(/[-]{2,}/gi, '-')}-${Date.now().toString()}.png`
      )
    });
  }

  if (scope.browser) {
    const button = await page.$(dashboard.selectors.logout);
    if (button) {
      await button.click();
    }

    const cookies = await page.cookies();
    if (cookies && cookies.length > 0) {
      await page.deleteCookie(...cookies);
    }
    await page.evaluate(() => localStorage.clear());
    await page.close();

    scope.context.currentPage = null;
  }
});

AfterAll(async () => {
  if (scope.browser) {
    await scope.browser.close();
  }
  scope.server.shutdown();
});

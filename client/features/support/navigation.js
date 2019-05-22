const scope = require('./scope');

const SELECTOR_TIMEOUT = 5000;

const goto = async (page = '') => {
  if (!scope.browser) {
    const isCIEnv = !!process.env.CI;
    scope.browser = await scope.driver.launch({
      ignoreHTTPSErrors: true,
      headless: isCIEnv,
      slowMo: isCIEnv ? 0 : 2,
      args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
    });
  }

  if (!scope.context.currentPage) {
    scope.context.currentPage = await scope.browser.newPage();
    scope.context.currentPage.setViewport({
      width: 1600,
      height: 1024,
    });
  }

  await scope.context.currentPage.goto(scope.host + page, {
    timeout: SELECTOR_TIMEOUT,
    waitUntil: 'networkidle2',
  });
};

module.exports = {
  goto,
  SELECTOR_TIMEOUT,
};

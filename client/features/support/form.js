const scope = require('./scope');

const blurField = async (selector) => {
  await scope.context.currentPage.$eval(selector, e => e.blur());
};

const clearField = async (selector) => {
  await scope.context.currentPage.evaluate(selector => {
    const item = document.querySelector(selector);
    item.focus();
    item.value = '';
    document.querySelector(selector).blur();
  }, selector);
};

const fillField = async (selector, value) => {
  const page = scope.context.currentPage;
  await page.focus(selector);
  await page.keyboard.type(value);
};

const clearAndFillField = async (selector, value) => {
  await clearField(selector);
  await fillField(selector, value);
};

const submit = async (selector, waitForNavigation = true) => {
  const page = scope.context.currentPage;
  await page.$eval(selector, form => form.submit());
  if (waitForNavigation) {
    await page.waitForNavigation();
  }
};

const click = async (selector) => {
  if (!selector.match(/^\//)) {
    await scope.context.currentPage.click(selector);
  } else {
    const target = await scope.context.currentPage.$x(selector);
    if (target.length === 0) {
      throw new Error(`Element not found: ${selector}`);
    }
    await target[0].click();
  }
};

module.exports = {
  blurField,
  clearAndFillField,
  clearField,
  click,
  fillField,
  submit,
};

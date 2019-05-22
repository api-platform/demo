const assert = require('assert');
const scope = require('./scope');

const containsText = async (selector, value) => {
  const label = await scope.context.currentPage.$eval(selector, el => el.innerText);

  return assert.ok(value.toString().trim().includes(label.toString().trim()));
};

const equal = async (selector, value) => {
  const label = await scope.context.currentPage.$eval(selector, el => el.innerText);

  return assert.equal(label.toString().trim(), value.toString().trim());
};

const inputValueEquals = async (selector, expectedValue) => {
  const actualValue = await scope.context.currentPage.$eval(selector, el => el.value);

  return assert.equal(actualValue, expectedValue);
};

const urlEquals = async (url) => {
  const pageUrl = await scope.context.currentPage.url();

  return assert.equal(pageUrl, scope.host + url);
};

module.exports = {
  containsText,
  equal,
  urlEquals,
  inputValueEquals,
};

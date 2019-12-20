const {Then, When} = require('cucumber');
const {book} = require('../pages');
const {assert, form, navigation, scope} = require('../support');

When('I go to the books list', async () => {
  await navigation.goto(book.url);
});

Then('I see a list of books', async () => {
  await scope.context.currentPage.waitForSelector(book.selectors.title, {
    visible: true,
    timeout: navigation.SELECTOR_TIMEOUT,
  });
  await assert.equal(book.selectors.title, 'Book List');
  await scope.context.currentPage.waitForSelector(book.selectors.id, {
    visible: true,
    timeout: navigation.SELECTOR_TIMEOUT,
  });
});

When('I click on a book id', async () => {
  await scope.context.currentPage.waitForSelector(book.selectors.id, {
    visible: true,
    timeout: navigation.SELECTOR_TIMEOUT,
  });
  await form.click(book.selectors.id);
  await scope.context.currentPage.waitForSelector(book.selectors.id, {
    hidden: true,
    timeout: navigation.SELECTOR_TIMEOUT,
  });
});

Then('I see a book', async () => {
  await scope.context.currentPage.waitForSelector(book.selectors.title, {
    visible: true,
    timeout: navigation.SELECTOR_TIMEOUT,
  });
  await assert.containsText(book.selectors.title, 'Show /books/');
});

Then('I see a {string} button', async (text) => {
  await scope.context.currentPage.waitForXPath(`//button[text() = '${text}']`, {
    visible: true,
    timeout: navigation.SELECTOR_TIMEOUT,
  });
});

When('I click on the {string} button', async (text) => {
  await form.click(`//button[text() = '${text}']`)[0];
});

Then('I wait {int} seconds', async (time) => {
  await scope.context.currentPage.waitFor(time * 1000);
});

Then('I see a cover', async () => {
  await scope.context.currentPage.waitForSelector(book.selectors.cover, {
    visible: true,
    timeout: navigation.SELECTOR_TIMEOUT,
  });
});

const { Then, When } = require('cucumber');
const { book } = require('../pages');
const { assert, navigation, scope } = require('../support');

When('I go to the books list', async () => {
    await navigation.goto(book.url);
});

Then('I see a list of books', async () => {
    await scope.context.currentPage.waitForSelector(book.selectors.title, {
        visible: true,
        timeout: navigation.SELECTOR_TIMEOUT,
    });
    await assert.containsText(book.selectors.title, 'Books List');
});

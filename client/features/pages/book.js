module.exports = {
  url: '/books/',
  selectors: {
    title: 'h1',
    id: 'table tbody tr:nth-of-type(1) th a',
    createButton: 'a[href="/books/create"]',
    cover: 'img[title="cover"]',
  },
};

const { setWorldConstructor } = require('cucumber');
const puppeteer = require('puppeteer');
const { scope } = require('./support');
const server = require('./server');

const World = function () {
  scope.driver = puppeteer;
  scope.context = {};
  scope.host = server.host;
  scope.server = server;
};

setWorldConstructor(World);

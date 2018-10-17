const express = require('express');
const httpShutdown = require('http-shutdown');
const path = require('path');
const https = require('https');
const fs = require('fs');

const app = express();

const serverPort = 5000;

const assetsPath = path.join(__dirname, '../build');
const indexPath = path.join(assetsPath, 'index.html');

app.use(express.static(assetsPath));

app.use((req, res) => {
  res.sendFile(indexPath);
});

const server = httpShutdown(
  https.createServer({
    rejectUnauthorized: false,
    key: fs.readFileSync('cert.key'),
    cert: fs.readFileSync('cert.crt'),
  }, app).listen(serverPort)
);

server.host = `https://localhost:${serverPort}`;

module.exports = server;

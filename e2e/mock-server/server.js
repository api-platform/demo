const { execSync } = require("child_process");
const https = require("https");
const fs = require("fs");
const path = require("path");

const MOCKS_DIR = path.join(__dirname, "..", "tests", "mocks");
const KEY_PATH = path.join(__dirname, "key.pem");
const CERT_PATH = path.join(__dirname, "cert.pem");

// Auto-generate self-signed certificates if they don't exist
if (!fs.existsSync(KEY_PATH) || !fs.existsSync(CERT_PATH)) {
  console.log("Generating self-signed certificates...");
  execSync(
    `openssl req -x509 -newkey rsa:2048 -keyout ${KEY_PATH} -out ${CERT_PATH} ` +
    `-days 365 -nodes -subj '/CN=mock-server' ` +
    `-addext 'subjectAltName=DNS:openlibrary.org,DNS:covers.openlibrary.org,DNS:gutendex.com'`
  );
}

const server = https.createServer(
  { key: fs.readFileSync(KEY_PATH), cert: fs.readFileSync(CERT_PATH) },
  (req, res) => {
    const host = req.headers.host?.split(":")[0] || "openlibrary.org";
    const url = new URL(req.url, `https://${host}`);

    // Try direct file path first (e.g. /books/OL2055137M.json)
    let filePath = path.join(MOCKS_DIR, host, url.pathname);

    // Handle openlibrary search: /search.json?q=Title Author&limit=10 -> search/Title-Author.json
    if (url.pathname === "/search.json" && url.searchParams.has("q")) {
      const query = url.searchParams.get("q").replace(/\s+/g, "-");
      filePath = path.join(MOCKS_DIR, host, "search", `${query}.json`);
    }

    // Handle gutendex search: /books?search=Asimov -> search/Asimov.json
    if (host === "gutendex.com" && url.pathname === "/books" && url.searchParams.has("search")) {
      const query = url.searchParams.get("search").replace(/\s+/g, "-");
      filePath = path.join(MOCKS_DIR, host, "search", `${query}.json`);
    }

    if (fs.existsSync(filePath)) {
      const ext = path.extname(filePath);
      res.writeHead(200, {
        "Content-Type": ext === ".json" ? "application/json" : "image/jpeg",
      });
      res.end(fs.readFileSync(filePath));
    } else {
      console.warn(`404: ${host}${req.url} (tried ${filePath})`);
      res.writeHead(404);
      res.end("Not found");
    }
  }
);

server.listen(443, () => console.log("Mock HTTPS server running on port 443"));

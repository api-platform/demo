/**
 * Better Auth database migration script.
 *
 * Creates the required tables if they don't exist.
 * Uses only the `pg` driver (no better-auth dependency needed at runtime),
 * so it works in the Next.js standalone production image.
 *
 * Run: node migrate.mjs
 * Requires: BETTER_AUTH_DATABASE_URL env var
 */

import pg from "pg";

const sql = `
CREATE TABLE IF NOT EXISTS "ba_user" (
  "id" text NOT NULL PRIMARY KEY,
  "name" text NOT NULL,
  "email" text NOT NULL UNIQUE,
  "emailVerified" boolean NOT NULL,
  "image" text,
  "createdAt" timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
  "updatedAt" timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE TABLE IF NOT EXISTS "ba_session" (
  "id" text NOT NULL PRIMARY KEY,
  "expiresAt" timestamptz NOT NULL,
  "token" text NOT NULL UNIQUE,
  "createdAt" timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
  "updatedAt" timestamptz NOT NULL,
  "ipAddress" text,
  "userAgent" text,
  "userId" text NOT NULL REFERENCES "ba_user" ("id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "ba_account" (
  "id" text NOT NULL PRIMARY KEY,
  "accountId" text NOT NULL,
  "providerId" text NOT NULL,
  "userId" text NOT NULL REFERENCES "ba_user" ("id") ON DELETE CASCADE,
  "accessToken" text,
  "refreshToken" text,
  "idToken" text,
  "accessTokenExpiresAt" timestamptz,
  "refreshTokenExpiresAt" timestamptz,
  "scope" text,
  "password" text,
  "createdAt" timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
  "updatedAt" timestamptz NOT NULL
);

CREATE TABLE IF NOT EXISTS "ba_verification" (
  "id" text NOT NULL PRIMARY KEY,
  "identifier" text NOT NULL,
  "value" text NOT NULL,
  "expiresAt" timestamptz NOT NULL,
  "createdAt" timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
  "updatedAt" timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE INDEX IF NOT EXISTS "ba_session_userId_idx" ON "ba_session" ("userId");
CREATE INDEX IF NOT EXISTS "ba_account_userId_idx" ON "ba_account" ("userId");
CREATE INDEX IF NOT EXISTS "ba_verification_identifier_idx" ON "ba_verification" ("identifier");
`;

const pool = new pg.Pool({
  connectionString: process.env.BETTER_AUTH_DATABASE_URL,
});

try {
  await pool.query(sql);
  console.log("Better Auth tables ready.");
} catch (error) {
  console.error("Better Auth migration failed:", error.message);
  process.exit(1);
} finally {
  await pool.end();
}

# MCP Server

* Status: accepted
* Deciders: @vincentchalamon

## Context and Problem Statement

API Platform 5.0 can expose resources as [Model Context Protocol](https://modelcontextprotocol.io) tools through the `mcp`
option of `#[ApiResource]`. The demo should show it from a real AI client (Claude Code, MCP Inspector, claude.ai): a
user asks to list books (public data), then to publish a review, which requires them to authenticate through the
standard MCP authorization flow (OAuth 2.1, browser login) instead of copying a token by hand.

## Considered Options

### Tools

Exposing the admin resources would show that a resource-level `security` cascades to the tools for free, but it would
promote handing destructive back-office operations to an LLM. The demo exposes end-user features instead: searching
books, getting a book, and reviewing a book on behalf of the authenticated user. Updating or deleting a review is not
exposed: their security relies on a Keycloak UMA permission bound to the REST URI (`request.getRequestUri()`), which is
always `/mcp` over MCP.

### Triggering the authorization

API Platform answers a denied tool call with a JSON-RPC error over HTTP 200, and hides tools denied by their `security`
expression from `tools/list`. MCP clients only start the OAuth flow on an HTTP 401 pointing to the protected resource
metadata (RFC 9728).

**Requiring a token on the whole `/mcp` endpoint** works with every client, but the user has to log in before reading
public data.

**Lazy authentication** keeps the public tools anonymous and challenges the client only when it calls a secured tool.
This is the pattern documented by Claude ("lazy authentication") and supported by MCP Inspector. It requires the secured
tool to stay listed for anonymous clients, and a small listener answering 401.

### Client registration

**Dynamic Client Registration** requires relaxing several Keycloak anonymous registration policies (trusted hosts, full
scope disabled, which removes the realm roles from the tokens), lets anyone create clients on a public demo (limited to
200), and is deprecated by the MCP specification since 2026-07-28.

**Client ID Metadata Documents** are the modern option of the specification, but are still experimental in Keycloak 26.

**A pre-registered public client** is the first option of the specification: the user only passes its client ID once
when adding the server.

## Decision Outcome

* `search_books` and `get_book` on the public `Book` resource, `add_review` on the public `Review` resource. `add_review`
  reuses the REST creation logic (`ReviewPersistProcessor`): author from the token, Keycloak UMA resource, Mercure update.
* **Lazy authentication**: `add_review` uses `securityPostDenormalize` instead of `security` so it stays listed, and
  `McpAuthorizationListener` answers `401` with `WWW-Authenticate: Bearer resource_metadata="..."` when an anonymous
  client calls a secured tool. It also adds `resource_metadata` to the 401 sent by the firewall for invalid tokens.
* `/.well-known/oauth-protected-resource(/mcp)` exposes the RFC 9728 metadata, pointing to the Keycloak realm.
* **Pre-registered public client** `api-platform-mcp` in Keycloak: PKCE (S256), consent required, loopback redirect URIs
  without port (Keycloak accepts any port on loopback, as Claude Code picks a random one), MCP Inspector and claude.ai
  callbacks.
* A dedicated `mcp` firewall only accepts tokens issued for the MCP server, as required by the specification: the
  `api-platform-mcp-audience` client scope adds the `api-platform-mcp` audience to the tokens of this client (Keycloak 26
  ignores the RFC 8707 `resource` parameter). It uses the Symfony `oidc` token handler with OIDC discovery.
* MCP sessions are stored in PostgreSQL (`cache.adapter.doctrine_dbal`), to be shared across php pods.

API Platform could answer the 401 itself and list secured tools to anonymous clients: the listener should then be
removed.

## Links

* [MCP authorization specification](https://modelcontextprotocol.io/specification/2025-11-25/basic/authorization)
* [API Platform MCP documentation](https://api-platform.com/docs/core/mcp/)
* [Claude lazy authentication](https://claude.com/docs/connectors/building/lazy-authentication)
* [RFC 9728 - OAuth 2.0 Protected Resource Metadata](https://www.rfc-editor.org/rfc/rfc9728)
* [Keycloak as MCP authorization server](https://www.keycloak.org/securing-apps/mcp-authz-server)

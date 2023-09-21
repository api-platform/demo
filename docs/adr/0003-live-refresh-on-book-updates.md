# Live Refresh on Book Updates

* Status: accepted
* Deciders: @gregoirehebert, @vincentchalamon

## Context and Problem Statement

When an admin creates, updates or removes a book, the users must instantly see this modification on the client.

## Considered Options

PostgreSQL implements a [Notify](https://www.postgresql.org/docs/current/sql-notify.html) command which sends a
notification event together with an optional "payload" string to each client application that has previously
executed a `LISTEN **_channel_**` for the specified channel name in the current database. This option implies a custom
PHP script on the API to handle the connection between the client and the database, which requires tests, performances
checks, security, etc. It also implies a PostgreSQL procedure which locks the API to this database system.

[WebSockets API](https://developer.mozilla.org/fr/docs/Web/API/WebSockets_API) is an advanced technology to open a
two-way interactive communication session between the user's browser and the server. [Caddy](https://caddyserver.com/)
is able to handle it, as many other servers and solutions. This would be a valid working solution.

[Meteor.js](https://www.meteor.com/) is an open source platform for seamlessly building and deploying Web, Mobile, and
Desktop applications in Javascript or TypeScript. Installed as an API Gateway, it would be a valid working solution too.

[Mercure](https://mercure.rocks/) is an open solution for real-time communications designed to be fast, reliable and
battery-efficient. As previous solutions, it would be a valid working one.

## Decision Outcome

Among all those good solutions found, [Mercure](https://mercure.rocks/) would be the most appropriate one thanks to its
integration in API Platform and Caddy. No extra server would be necessary, and it's easily usable with API Platform.

## Links

* [PostgreSQL Notify](https://www.postgresql.org/docs/current/sql-notify.html)
* [WebSockets API](https://developer.mozilla.org/fr/docs/Web/API/WebSockets_API)
* [Caddy](https://caddyserver.com/)
* [Meteor.js](https://www.meteor.com/)
* [Mercure](https://mercure.rocks/)

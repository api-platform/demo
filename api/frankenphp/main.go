package main

import (
	caddycmd "github.com/caddyserver/caddy/v2/cmd"

	// plug in Caddy modules here.
	_ "github.com/caddyserver/caddy/v2/modules/standard"
	_ "github.com/darkweak/souin/plugins/caddy"
	_ "github.com/darkweak/storages/otter/caddy"
	_ "github.com/darkweak/storages/redis/caddy"
	_ "github.com/dunglas/frankenphp/caddy"
	_ "github.com/dunglas/mercure/caddy"
	_ "github.com/dunglas/vulcain/caddy"
)

func main() {
	caddycmd.Main()
}

vcl 4.0;

import std;

backend default {
  .host = "api";
  .port = "80";
  # Health check
  #.probe = {
  #  .url = "/";
  #  .timeout = 5s;
  #  .interval = 10s;
  #  .window = 5;
  #  .threshold = 3;
  #}
}

# Hosts allowed to send BAN requests
acl ban {
  "localhost";
  "php";
}

sub vcl_backend_response {
  # Ban lurker friendly header
  set beresp.http.url = bereq.url;

  # Add a grace in case the backend is down
  set beresp.grace = 1h;
}

sub vcl_deliver {
  # Don't send cache tags related headers to the client
  unset resp.http.url;
  # Uncomment the following line to NOT send the "Cache-Tags" header to the client (prevent using CloudFlare cache tags)
  #unset resp.http.Cache-Tags;

  # Add a debug to see the number of HITS (0 means MISS)
  # Disable it in production
  if (obj.hits > 0) {
    set resp.http.ApiPlatform-Cache = "HIT";
  } else {
    set resp.http.ApiPlatform-Cache = "MISS";
  }

  set resp.http.ApiPlatform-Cache-Hits = obj.hits;

  return (deliver);
}

sub vcl_recv {
  # Remove the "Forwarded" HTTP header if exists (security)
  unset req.http.forwarded;

  # To allow API Platform to ban by cache tags
  if (req.method == "BAN") {
    if (client.ip !~ ban) {
      return(synth(405, "Not allowed"));
    }

    if (req.http.ApiPlatform-Ban-Regex) {
      ban("obj.http.Cache-Tags ~ " + req.http.ApiPlatform-Ban-Regex);

      return(synth(200, "Ban added"));
    }

    return(synth(400, "ApiPlatform-Ban-Regex HTTP header must be set."));
  }

  if (req.method != "GET" && req.method != "HEAD") {
    # Only cache GET or HEAD requests. This makes sure the POST/PUT/DELETE requests are always passed.
    return (pass);
  }

  return(hash);
}

# From https://github.com/mattiasgeniar/varnish-4.0-configuration-templates/blob/master/default.vcl
sub vcl_hash {
  # Called after vcl_recv to create a hash value for the request. This is used as a key
  # to look up the object in Varnish.

  hash_data(req.url);

  if (req.http.host) {
    hash_data(req.http.host);
  } else {
    hash_data(server.ip);
  }

  # hash cookies for requests that have them
  if (req.http.Cookie) {
    hash_data(req.http.Cookie);
  }
}

# From https://github.com/varnish/Varnish-Book/blob/master/vcl/grace.vcl
sub vcl_hit {
  if (obj.ttl >= 0s) {
    # Normal hit
    return (deliver);
  } elsif (std.healthy(req.backend_hint)) {
    # The backend is healthy
    # Fetch the object from the backend
    return (fetch);
  } else {
    # No fresh object and the backend is not healthy
    if (obj.ttl + obj.grace > 0s) {
      # Deliver graced object
      # Automatically triggers a background fetch
      return (deliver);
    } else {
      # No valid object to deliver
      # No healthy backend to handle request
      # Return error
      return (synth(503, "API is down"));
    }
  }
}

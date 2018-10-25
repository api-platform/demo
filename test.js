/** Extract the hub URL from the Link header */
function extractHubURL(response) {
    const linkHeader = response.headers.get('Link');
    if (!linkHeader) return null;

    const matches = linkHeader.match(
        /<([^>]+)>;\s+rel=(?:mercure|"[^"]*mercure[^"]*")/
    );

    return matches && matches[1] ? new URL(matches[1]) : null;
}

// Has this header `Link: <https://example.com/hub>; rel="mercure"`
fetch('https://example.com/books/1')
    .then(response => {
        const h = extractHubURL(response);
        if (h) {
            h.searchParams.append('topic', 'https://example.com/books/1');

            const es = new EventSource(h);
            es.onmessage = e => console.log(e);
        }

        // do something with the response
    });


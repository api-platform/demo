# Book Fields

* Status: accepted
* Deciders: @gregoirehebert, @vincentchalamon

## Context and Problem Statement

Considering the Book resource with a `book` property, exposing an IRI of a book from the BNF API. With this
architecture, the client could request this IRI to retrieve the book data.

But how can the client filters the books by title and author from our API if we don't handle those properties?

## Considered Options

A first option would be to let the client request on the BNF API, retrieve a collection of books IRI, then use it to
filter the books collection on our API (using `book` query filter). This approach cannot work properly because the BNF
API will return IRIs which won't be registered in our API.

Another option would be to enable custom filters on our API (`title` and `author`). Then, the API will call the BNF
API to retrieve a collection of books IRI, and use it in a Doctrine query to filter the API Book objects by the `book`
property. It exposes the API to a performance issue if the BNF API returns a huge amount of IRIs. Restricting this
collection (e.g.: limiting the BNF request to 100 results) may ignore some books arriving later.

To fix this last option issues, another option is to list all API Book IRIs, then filter the BNF API by title, author
and this collection to retrieve only IRIs that match those filters. But the performance issue still remains if our API
manages a huge collection of books.

Finally, the last considered option would be to duplicate the title and author properties on our API for filtering
usage.

## Decision Outcome

The last considered option has been selected as the best compromise in such situation. The API will call the BNF API on
Book creation, retrieve the `title` and `author` properties and save them for local filtering usage.

# Book.reviews Property as Collection IRI

* Status: accepted
* Deciders: @gregoirehebert, @vincentchalamon

## Context and Problem Statement

A Book may have a lot of reviews, like thousands. Exposing the reviews on a Book may cause an over-fetching issue.

The client may have to show the reviews, or may not. For instance, we want the front client to show the reviews on a
Book page, but on the admin client it's not necessary.

How can we expose a Book reviews without provoking any under/over-fetching, and without requesting the reviews on the
database when it's not necessary?

## Considered Options

Thanks to [Vulcain](https://vulcain.rocks/), it is possible to preload some data and push them to the client. But how
the `Book.reviews` data should be exposed?

The first considered option would be to only expose the IRIs of each review from a Book. But it doesn't solve the
over-fetching issue if the Book has a lot of reviews. Also, this list wouldn't be paginated nor filtered. These would
be huge limitations over the reviews collection.

Another considered option is to expose the IRI of the Book reviews (e.g.: `/books/{id}/reviews`), and let
[Vulcain](https://vulcain.rocks/) request it when necessary. This IRI would expose a paginated and filtered list of
reviews related to this Book. It would also be possible to manage the authorization differently than Review main
endpoint, for admin usage for instance.

## Decision Outcome

The last option would be the best solution as it respects the
[Hydra Spec](https://www.hydra-cg.com/spec/latest/core/#example-5-using-json-ld-s-type-coercion-feature-to-create-idiomatic-representations)
and prevent any over-fetching.

The Book JSON-LD response would return an IRI for `reviews` property, which can be parsed with
[Vulcain](https://vulcain.rocks/) to preload them, and keep any pagination and filtering features.

## Links

* [Vulcain](https://vulcain.rocks/)
* [Hydra Spec - Using JSON-LD's type-coercion feature to create idiomatic representations](https://www.hydra-cg.com/spec/latest/core/#example-5-using-json-ld-s-type-coercion-feature-to-create-idiomatic-representations)

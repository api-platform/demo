# Save Book Data from BNF API

* Status: accepted
* Deciders: @gregoirehebert, @vincentchalamon

## Context and Problem Statement

Some Book data come from the BNF API (cf. [Book Fields](0000-book-fields.md)). How to retrieve and aggregate them
before saving a Book object in the database?

## Considered Options

A first option would be to use a custom entity listener with Doctrine. This approach would let us complete the Book
entity right before save by calling the BNF API and retrieving the properties. But using those lifecycle callbacks are
a bad practice and "_are supposed to be the ORM-specific serialize and unserialize_"
(cf. ["Doctrine 2 ORM Best Practices" by Ocramius](https://ocramius.github.io/doctrine-best-practices/)).

Another option would be to use a custom [State Processor](https://api-platform.com/docs/core/state-processors/) to
retrieve the data from the BNF API, then update and save the Book object.

## Decision Outcome

The last solution is preferred as it's the recommended way by API Platform to handle a custom save on a resource.

## Links

* [Book Fields ADR](0000-book-fields.md)
* ["Doctrine 2 ORM Best Practices" by Ocramius](https://ocramius.github.io/doctrine-best-practices/)
* [API Platform State Processor](https://api-platform.com/docs/core/state-processors/)

Architecture Decision Record 001: Routing PID context pages
===========================================================

Context
-------

The majority of routes within programmes live within the context of a PID, e.g.
`/programmes/:pid/series`, `/programmes/:pid/segments`, `/programmes/:pid/clips`
display a the series, segments and clips belonging to the programme with PID
:pid respectively. In this document `:pid` shall be refereed to as the
"context PID" and the entity with that PID shall be the "context entity".

These pages are often only relevant to a subset of object types - mostly
Programme or its subtypes, but occasionally Groups - in other cases the page
should 404. For instance:

* /programmes/:pid/series is only valid if the :pid is for a ProgrammeContainer
* /programmes/:pid/segments is only valid if the :pid is for a
  ProgrammeContainer
* /programmes/:pid/clips is only valid for if the :pid is for
  a ProgrammeContainer or Episode


These routes may also be considered invalid if the context entity has options
that mark the entity as being a redirect. This is usually used for CBBC
and Cbeebies programmes, e.g. `/programmes/b006qgb3/clips` redirects to the
programme on the CBBC website. This functionality must be applied across all
routes beneath a PID.

Thus looking up this context entity and working out if it is valid for a given
route - either because the entity type is not relevant or because a redirect is
specified in the options - is a common behavior and we should not have to
repeat it throughout the application.


Decision
--------

[ARD-001](adr-001-routing-find-by-pid-pages.md) defined the notion that
Controllers can have a domain entity passed into them. We shall follow this
pattern for context pages.

Symfony has a mechanism called ArgumentResolvers that allow the application to
customize the arguments a Controller receives. You are familiar with this
already - it is Symfony's built-in `RequestAttributeValueResolver` that maps
route arguments to controller arguments.

We shall write an ArgumentResolver that shall be triggered when it finds a
parameter typehinted as a Programme, Group or Service (or one of their sub
types). This ArgumentResolver shall then look for the PID route argument, and
use it to look up an entity of the requested type and if found return the
requested entity. If the entity of the requested type does not exist then the
ArgumentResolver shall throw a 404, or if the entity has redirects then the
redirect shall trigger.

From the view of a developer writing new routes, they can request an entity of a
given type by creating a route that referencing a PID and create a controller
that typehints the entity type they require and they shall get that entity
available to use within their controller.

Cases where the required entity types do not fit cleanly into a type hierarchy
such as "ProgrammeContainer or Episode" could be dealt with by creating an
Interface that is implemented by all relevant types, or by requesting a
supertype - "Programme" is a non-strict supertype of "ProgrammeContainer or
Episode" and then the filtering to exclude irrelevant types - in this case
excluding "Clip" within the controller. This case only occurs in three cases
that we shall be migrating, two of which are the aforementioned
"ProgrammeContainer or Episode" case.


Status
------

Accepted


Consequences
------------

* Typehinting requiring a domain entity in a controller shall result in
  receiving an instance of that entity. This is the same behaviour as in
  FindByPid routes so there is a consistent experience across all Controllers.
* Entity lookup, and redirect checks are localised in a single place rather than
  need to be repeated in all Controllers.
* The FindByPid route shall not use this ArgumentResolver, despite having a
  similar end-behavior, thus redirect logic shall have to be repeated in two
  places. This is not ideal but this logic is not complex (~6 lines of code).
* Custom ArgumentResolvers may be non-obvious place to store data retrieval
  logic to developers who are new to the codebase.

Architecture Decision Records
=============================

The folder contains a list of architecture decisions that effect the project.
By detailing the decisions we have made and the reasons for them, future team
members can understand why things are the way they are. The aim is to give
sufficient context to the problems we face so that in the future we don't end up
recreating the mistakes of the past due to insufficent knowledge.

Read
http://thinkrelevance.com/blog/2011/11/15/documenting-architecture-decisions for
more information about the whats and whys of ARDs.

Contents
--------

* [001: Routing Find by PID pages](adr-001-routing-find-by-pid-pages.md): How
  `/programmes/:pid` is routed
* [002: Routing PID context pages](adr-002-routing-pid-context-pages.md):
  How `/programmes/:pid/clips` and other pages beneath `/programmes/:pid` are
  routed
* [003: Serving Programme Options Redirects](adr-003-serving-programme-options-redirects.md):
  How redirects configured within programme options are handled.


Template
--------

Each ADR should follow the following standard format:


```md
Architecture Decision Record xxx: Title
=======================================

Context
-------


Decision
--------


Status
------


Consequences
------------

```

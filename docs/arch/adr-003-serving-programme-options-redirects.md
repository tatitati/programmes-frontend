Architecture Decision Record 003: Serving Programme Options Redirects
=====================================================================

Context
-------

Some entities that exist in PIPs do not wish to be surfaced by /programmes.
The most common example of this is programmes broadcast on CBBC or Cbeebies;
these programmes are instead exposed by the CBBC website as the needs of young
childrens programming is radically different to rest of the BBC's output. To
deal with this, Programme Options offer the ability to redirect a Programme and
all pages underneath it to a location defined by editorial staff. 

Checking for this option and triggering the redirect needs to happen in two
places.

* In `EventSubscriber\FindByPidRouterSubscriber` which is used when we hit the
  main find by pid route - `/programmes/:pid`. When the entity used for the find
  by pid route is found we should check the Programme Options and trigger a
  redirect if required. For more information about the purpose of this
  EventSubscriber read [ADR-001](adr-001-routing-find-by-pid-pages.md).
* In `ArgumentResolver\ContextEntityByPidValueResolver` which is used when we
  hit any other route that contains a `:pid` parameter and whose controller
  contains an argument that is a CoreEntity or a subclass thereof. When the
  entity is found we should check the Programme Options and trigger a
  redirect if required. For more information about the purpose of this
  ArgumentResolver read [ADR-002](adr-002-routing-pid-context-pages.md).

Decision
--------

`FindByPidRouterSubscriber` triggers at a point in the event lifecycle where we
can call `$event->setResponse(new Response('', $status, ['location' => $location]));`
which will result Symfony returning that response, rather than carrying on with
the rest of the event lifecycle. `ContextEntityByPidValueResolver` offers no
such escape hatch. Throwing exceptions is the only way to signal something other
than "return an entity that will be passed into the controller".

Rewriting these two components so we can return a response from an event hook
without using exceptions is complex and introduces convoluted coupling between
these components.

Symfony already has a built-in mechanism for using exceptions to trigger 4xx
responses. It is strightforward to extend that to allow it to trigger 3xx
responses too. 3xx responses must not make calls to ORB/Branding as that data
is not needed to serve a 3xx response. These responses are also should not
trigger any error logs as they are expected outcomes.

Currently Symfony supports supressing the logging of 404 errors when using the
`fingers_crossed` error handler. This is done through "Activation Strategy"
classes that control if an exception should trigger a log message. We will have
to create a custom activation strategy that supresses the logging of 3xx
exceptions in addition to 404 exceptions. If https://github.com/symfony/symfony/pull/25533
is merged upstream then this custom activation strategy can be removed and
replaced with configuration.

Status
------

Accepted


Consequences
------------

* Throwing a `ProgrammeOptionsRedirectHttpException` shall result in a 3xx
  response.
* This exception shall be handled by Symfony's built-in exception handler in the
  same way as other instances of `HttpException`.
* Custom error handling logic is a non-obvious place to store these consequences
  to developers who are not familar with Symfony's event lifecycle.
* Once in-progress PRs in Symfony are merged we can leverage built-in config for
  log supression rather than having to roll our own.

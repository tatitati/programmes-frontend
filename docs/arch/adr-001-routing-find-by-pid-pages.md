Architecture Decision Record 001: Routing Find by PID pages
===========================================================

Context
-------

`/programmes/:pid` is a route that may serve one of many page types. That PID
may identify a Programme, Group, Version or Segment. Programme is
further split into Top-level Container, other Container, Episode and Clip. Group
is split into Collection, Season, Gallery and Franchise. Thus there are 10
potential page types that `/programmes/:pid` may direct render. Each one of
these page types has their own independent controller.

We do not know which of these page types is required until we make a data query
to determine the entity type that the PID refers to. We also want to ensure the
number of data queries are kept to a minimum throughout the lifetime of a
request (fewer queries means faster). Ideally this data query should be
useful to and available within the remainder of the request lifecycle.

It must be possible to determine which route is currently being served so we can
have statistic reporting on a per-page-type basis (i.e. we should be able to
tell the difference between an Episode and a Clip page).

The Controllers for these page types tend to be quite large. Currently
/programmes v2 stores many controller actions in a single file, this file is
unreasonably large and we should seek to avoid this in the future for the sake
of code maintainability.


Decision
--------

It is not possible to solve this in the standard Symfony way of specifying a
`_controller` value within the routing configuration file (which sets the
`_controller` attribute of the current request, which maps to the
name of the controller to call). This is because we do not know the controller
name in advance - it requires a data query to determine. However Symfony does
provide a mechanism to dynamically set the `_controller` value prior to calling
the controller function.

Symfony provides an Event hook system. We shall create an event that fires on
the `REQUEST` hook - after the standard routing resolution (for mapping a url
route) happens, but before the controller function is called. At this point we
have the ability to run arbitary code and set the `_controller` value on the
current Request object.

This event shall run find-by-pid queries against the CoreEntity, Version and
Segment data sources and if an entity is found then the `_controller` value
shall be set to the relevant controller name and Symfony's request loop shall
continue as standard.

Symfony also provides a mechanism for passing values from routing resolution
into controllers - you are familiar with this already - naming a route
parameter `abc` and  having a controller function accept a parameter called
`$abc` will result in the route parameter being available in the controller.

In addition to updating the `_controller` attribute we shall add the entity to
the attribute bag with the key name 'entity'. Then our controller functions can
accept a `$entity` parameter which when called shall be the domain object we
requested.

This means that we can make a query once the result is passed around rather than
having to make multiple queries for the same data.


Status
------

Accepted


Consequences
------------

* We shall create multiple invokable controllers as we would anywhere else in
  the application.
* FindByPid Controllers will accept an entity parameter.
* We only need a single data request to get entity information.
* Event hooks are application specific and thus the changes they make to the
  application state are non-obvious just by looking at the code.

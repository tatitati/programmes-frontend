Adverts
=======

Thanks to the unique way the BBC is funded, the BBC website is ad-free if
you're looking at it from within the UK. However this changes if you are
overseas and viewing content primarily designed for overseas audiences. The
idea being that UK-focused content is paid for by the license fee and if you're
in the UK you've already paid for that.

We may show adverts on the TLEC, Series, Episode, and Clip pages if you are
viewing an "International" programme from outside of the UK. As of  February
2018, this applies to BBC World News, BBC World Service, and the various
language-specific World Service radio stations.

There are two advert slots that may appear on a page:

* The [leaderboard](https://theonlineadvertisingguide.com/ad-size-guide/728x90/):
    a board that sits across the top of the page.
* The [MPU](https://theonlineadvertisingguide.com/ad-size-guide/300x250/): a box
  that sits in a 300px wide column within the page.


Technical Implementation
------------------------

Advert rendering is a two step process. Initially one or more advert slots -
which are `div` elements with a specific ID and a small `<script>` tag to
register the slot - are injected into a page, and then a piece of Javascript
(owned by the bbcdotcom team and included in the ORB) is ran to transform those
slots into adverts. It is the responsibility of the /programmes team to render
the slots and call the bbcdotcom JavaScript, we do not need to deal with
anything beyond that.

The HTML for advert slots are only loaded into pages if the context programme
for the page belongs to an "International" network (check the is_international
field of the network table in the DB).

Then the bbcdotcom JavaScript checks to see if the user is based outside the UK,
and if so shall render an advert into the slot.

Additional information can be found in the guides provided by the bbcdotcom
team at <https://confluence.dev.bbc.co.uk/pages/viewpage.action?pageId=85079452>

### Twig details

Control of adverts is centralised within the
[`BbcDotComExtension`](../src/Twig/BbcDotComExtension.php) Twig extension. This
provides Twig functions to register advert slots, and output advert content.


### Blocking FIG loader

Normally the FIG JavaScript module that provides client gelolocation data is
loaded asynchronously. However for pages that may display adverts we must load
the FIG in 'blocking' mode as the advert module does not yet support getting
fig data asynchronously.

This is done within the base templates (`base_ds2013.html.twig` and
`base_ds_amen.html.twig`) is advert blocks are defined for the page and if the
context programme belongs to an "International" network. Only pages that may
have adverts rendered on them have this behaviour, pages where adverts are
never rendered do not use this less-performant behaviour.

Once the adverts JavaScript supports using the non-blocking FIG then the calls
to set `window.orb_fig_blocking = true;` should be removed.

See <https://navigation.api.bbc.co.uk/docs/fig.md#using-a-blocking-fig> for
more information.

Testing Adverts
---------------

[BBC Click on World News](https://www.bbc.co.uk/programmes/n13xtmd5) is a good
example of an "International" programme which has adverts.

To test adverts you will need to pretend you are based outside of the UK while
visiting the website.

Details of how to test adverts are on [this confluence page](https://confluence.dev.bbc.co.uk/display/programmes/Testing+Adverts)


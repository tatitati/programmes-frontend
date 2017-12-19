/programmes
===========

/programmes version 3. Finally.

Static Assets
-------------

Using Yarn allows the dependencies to be properly locked, and can abstract away
the need to install other packages globally.
[Install Yarn](https://yarnpkg.com/en/docs/install) first.

Then perform `yarn install`

To perform the development tasks
`yarn run gulp`

To perform the dist tasks (for ci)
`yarn run gulp-ci`

To watch for file changes
`yarn run watch`

Composer and cache:clear
------------------------

**The problem:** If you run composer install then will be executed a group of 
console commands. One of these is `app/console cache:clear`. This command try 
to connect to Redis server in localhost, so composer consider that redis 
server is in the environment in which is executed, but is only inside the 
VM, giving errors. Neither Composer nor `cache:clear` accept options to 
handle this in a better way.
 
**Replication:** If you try to run the `clear:cache` console command (with `composer install`) 
from outside de VM you will have you an error as we cannot connect to redis inside 
the VM using composer from outside. 
  
**Solution:** run `composer install` inside the VM. 



Translations
------------

### Updating translation files with new placeholders

This is a two step process. You need to update both the programmes.pot template
and the English translation file. Sorry, but that's the way GetText expects you
to work.


#### To add an entry

Open `translations/programmes/programmes.pot` and add your new
entry.

```
#.The English translation
msgid "my_shiny_new_entry"
msgstr ""
```

The comment should contain the full english translation and is needed as
guidance for translators in other languages.

**msgid can only contain the characters [A-Za-z0-9_:-].** This is a limitation
of our translate-tool app and not the PO file format itself.

Entries in the template file **should be in alphabetical order**. This is so
that we can reduce the potential for merge noise, allowing us to focus on
specific changes rather than automatic re-ordering. You must either add your
entries in the correct order, or add them all at the bottom, then run
`scripts/translate-alphabetiseAll.sh programmes` to move them into the correct
ordering.

After saving the template file, run
`scripts/translate-updateFromTemplate.sh programmes` to add the new entries to
all translation files. Then edit the en.po file and add your english
translation.

```
#.The English translation
msgid "my_shiny_new_entry"
msgstr "The English translation"
```


#### To remove an entry

Remove the entry from the programmes.pot template and run
`scripts/translate-updateFromTemplate.sh programmes`


### Updating translation files with new translations (supplied by a translator in .po format)

There's a script that does most of this for you -
`translate-updateLanguageFromSuppliedPO.sh`. For example, to update the Welsh
translation file (language code "cy") from a new file supplied by a translator:

```sh
./scripts/translate-updateLanguageFromSuppliedPO.sh ~/Downloads/new-translations-cy_GB.po programmes cy
```

Once you've done that, you'll probably want to run a diff to check everything
is OK and remove any superfluous headers added by the .po editor by hand. But
the script should take care of any changes in ordering or new/wrong entries that
may have been added by the translator's .po editor. It uses msgmerge from the
command line internally.

Profiling
-----------
There is a long-lived (hopefully) profiling branch named profiling-build which brings
in tideways (basically xhprof updated for PHP7), a GUI and a few other things
which you can run on Cosmos INT. It uses https://github.com/bbc/programmes-xhprof to setup the profiling.
 Got to that repository to see the available configuration options.

How to do this:
Checkout the profiling-build branch, rebase it on master and deploy this branch to INT. This assumes that the 
code you want to profile is on master of course. 

Visit https://programmes-frontend.int.api.bbc.co.uk/whatever/your/route/is?__profile=1 (Note the double underscore).
Load that at least 5 times to make sure that everything that should be cached is cached.
Now visit https://programmes-frontend.int.api.bbc.co.uk/xhprof/xhprof_html/index.php .
You should see a list of your visits along with a load of metrics on execution. 

Fixtures and scenarios
-----------
The codebase has the ability to switch to a fixture database and a set of fixtured HTTP calls dynamically on the
INT and TEST environments. 

To load a scenario, simply visit the fixtured page with the query string __scenario=scenario-name in the URL

To view a page using the fixture database (but not fixtured HTTP calls) use the query string __scenario=browse

To create a scenario/fixtures there are a few steps. Firstly you will need to create a file named "fixture.ini" in
the script folder on your cloud sandbox. Populate it with the following
```bash
PASSWORD="the_password"
```  
you can obtain the password from the usual place.

Now cd to the checkout out programmes frontend code and run ```./script/fixture```. The script can do 3 things, 
documented in the help output. Generate a new scenario (the code will error if the scenario name already exits). 
Regenerate an existing scenario (this basically uses all the existing HTTP fixtures, but creates new ones for any
unfixtured URLs). Or delete a scenario. Have fun!

License
-------

This repository is available under the terms of the Apache 2.0 license.
View the [LICENSE file](LICENSE) file for more information.

Copyright (c) 2017 BBC

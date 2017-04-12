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


Translations
------------

### Updating translation files with new placeholders

This is a two step process. You need to update both the programmes.pot template
and the English translation file. Sorry, but that's the way GetText expects you
to work.


#### To add an entry

Open `app/Resources/translations/programmes/programmes.pot` and add your new
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


License
-------

This repository is available under the terms of the Apache 2.0 license.
View the LICENSE file for more information.

Copyright (c) 2017 BBC

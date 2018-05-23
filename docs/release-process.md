Release Process
===============

/programmes uses a variation of [GitHub Flow](https://guides.github.com/introduction/flow/).
Code shall be written in branches and raised as PRs before being merged into one
of our two long-running branches.

While it should be the case that only stable code is merged into the main
branch, there is a difference between stable and correct from a business
perspective. We are also aware that sometimes bugs will make their way to the
LIVE environment and that we require a mechanism to release a single hotfix to
LIVE without also releasing in-progress work.

/programmes shall have two long-running branches:

* `develop` - The primary branch. All PRs for day-to-day work must be merged
  into the develop branch.
* `master` - The representation of what is currently deployed to the LIVE
  environment. If an emergency hotfix to LIVE is required, then the PR must
  target the master branch. The fix will then be backported into the develop
  branch.

In addition to this, release branches will be created when a scheduled release 
is done. 

For more information see [Git Branching](git-branching.md)

/programmes shall intend to release to LIVE at a regular cadence of once every
two weeks at the end of a sprint. In addition to these regular releases, we may
release at other times due to unforseen circumstances to fix critical bugs.


Version Numbers
---------------

/programmes releases shall be tagged using [semantic versioning](http://semver.org/).
The tagged version number shall increase in one of two ways:

* Standard end-of-sprint releases shall increase the MINOR version number e.g.
  v3.1.0 to v3.2.0
* Emergency hotfixes shall increase the PATCH version number e.g. v3.1.0 to
  v3.1.1


Standard Release Process
---------------------

For a standard release, which will normally take place at the end of a two week sprint cycle, 
we shall roll up our existing changes and cut a release from the `develop` branch. 

This is a multi step process that ends with develop merged into master. 


Ensure you've pushed everything you have locally and you've got the `develop`
branch checked out:

```sh
git checkout develop && git pull --all --ff-only
```

Merge master into develop to ensure the two branches are in sync and that any
hotfixes applied to `master` are also in `develop`.

```sh
git merge master --ff-only
```

Now create a release branch named release-v3.x.y and push it.

```sh
git checkout -b release-v3.x.y 
git push --set-upstream origin release-v3.x.y 
```
 
Build and release this to the test environment. The test team should now run regression checks
against this code. If any bugs are found, fix them and merge the fixes to the release branch.  

Once the release branch is ready to go live, run the following set of commands:

```sh
git checkout master && git pull && git merge release-v3.x.y --ff-only && git push
```

If any hotfixes were merged into the release branch, ensure they are also merged to develop at 
this point:

```sh
git checkout develop 
git pull --all --ff-only 
git merge master
git push
```


Trigger a Jenkins build based off the master branch, and deploy it to the INT
and TEST environments.

Go to the [GitHub Releases page](https://github.com/bbc/programmes-frontend/releases)
and create a new release. The tag version should be a semver tag e.g. "v3.3.0".
Each per-sprint release should increment the minor version number and reset the
patch number, e.g. the sprint release after "v3.2.1" should be "v3.3.0".

The title of the release must be in the format: "Version number - Cosmos release number"

Write a short description detailing the major changes in this sprint.


Emergency Hotfix Process
------------------------

Emergency hotfixes should go through the standard PR process however the change
must be based off the `master` branch and when creating the PR it must be merged
into the `master` branch.

Once your PR has been reviewed and merged into `master`, trigger a Jenkins build
based off the master branch, and deploy it to the INT and TEST environments.

Go to the [GitHub Releases page](https://github.com/bbc/programmes-frontend/releases)
and create a new release. The tag version should be a semver tag e.g. "v3.0.0".
A hotfix should increment the patch version number, e.g. the
sprint release after "v3.2.0" should be "v3.2.1".

The title of the release must be in the format: "Version number - Cosmos release number"

Write a short description detailing the major changes in this sprint.


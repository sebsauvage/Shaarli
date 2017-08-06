**WORK IN PROGRESS**

It's important to understand how Shaarli branches work, especially if you're maintaining a 3rd party tools for Shaarli (theme, plugin, etc.), to be sure stay compatible.

## `master` branch

The `master` branch is the development branch. Any new change MUST go through this branch using Pull Requests.

Remarks:

- This branch shouldn't be used for production as it isn't necessary stable.
- 3rd party aren't required to be compatible with the latest changes.
- Official plugins, themes and libraries (contained within Shaarli organization repos) must be compatible with the master branch.
- The version in this branch is always `dev`.

## `v0.x` branch

This `v0.x` branch, points to the latest `v0.x.y` release.

Explanation:

When a new version is released, it might contains a major bug which isn't detected right away. For example, a new PHP version is released, containing backward compatibility issue which doesn't work with Shaarli.

In this case, the issue is fixed in the `master` branch, and the fix is backported the to the `v0.x` branch. Then a new release is made from the `v0.x` branch.

This workflow allow us to fix any major bug detected, without having to release bleeding edge feature too soon.

## `latest` branch

This branch point the latest release. It recommended to use it to get the latest tested changes.

## `stable` branch

The `stable` branch doesn't contain any major bug, and is one major digit version behind the latest release.

For example, the current latest release is `v0.8.3`, the stable branch is an alias to the latest `v0.7.x` release. When the `v0.9.0` version will be released, the stable will move to the latest `v0.8.x` release.

Remarks:

- Shaarli release pace isn't fast, and the stable branch might be a few months behind the latest release.

## Releases

Releases are always made from the latest `v0.x` branch.

Note that for every release, we manually generate a tarball which contains all Shaarli dependencies, making Shaarli's installation only one step.

## Advices on 3rd party git repos workflow

### Versioning

Any time a new Shaarli release is published, you should publish a new release of your repo if the changes affected you since the latest release (take a look at the [changelog](https://github.com/shaarli/Shaarli/releases) (*Draft* means not released yet) and the commit log (like [`tpl` folder](https://github.com/shaarli/Shaarli/commits/master/tpl/default) for themes)). You can either:

   - use the Shaarli version number, with your repo version. For example, if Shaarli `v0.8.3` is released, publish a `v0.8.3-1` release, where `v0.8.3` states Shaarli compatibility and `-1` is your own version digit for the current Shaarli version.
   - use your own versioning scheme, and state Shaarli compatibility in the release description.

Using this, any user will be able to pick the release matching his own Shaarli version.

### Major bugfix backport releases

To be able to support backported fixes, it recommended to use our workflow:

```bash
# In master, fix the major bug
git commit -m "Katastrophe"
git push origin master
# Get your commit hash
git log --format="%H" -n 1
# Create a new branch from your latest release, let's say v0.8.2-1 (the tag name)
git checkout -b katastrophe v0.8.2-1
# Backport the fix commit to your brand new branch
git cherry-pick <fix commit hash>
git push origin katastrophe
# Then you just have to make a new release from the `katastrophe` branch tagged `v0.8.3-1`
```

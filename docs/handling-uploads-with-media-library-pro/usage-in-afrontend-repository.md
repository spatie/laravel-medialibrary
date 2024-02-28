---
title: Usage in a frontend repository
weight: 9
---

If you can't install the package using `composer` because, for example, you're developing an SPA, you can download the packages from GitHub Packages.

## Registering with GitHub Packages

You will need to create a Personal Access Token with the `read:packages` permission on the GitHub account that has access to the [spatie/laravel-medialibrary-pro](https://github.com/spatie/laravel-medialibrary-pro) repository. We suggest creating an entirely new token for this and not using it for anything else. You can safely share this token with team members as long as it has only this permission. Sadly, there is no way to scope the token to only the Media Library Pro repository.

Next up, create a `.npmrc` file in your project's root directory, with the following content:

_.npmrc_

```
//npm.pkg.github.com/:_authToken=github-personal-access-token-with-packages:read-permission
@spatie:registry=https://npm.pkg.github.com
```

Make sure the plaintext token does not get uploaded to GitHub along with your project. Either add the file to your `.gitignore` file, or set the token in your `.bashrc` file as an ENV variable.

_.bashrc_

```
export GITHUB_PACKAGE_REGISTRY_TOKEN=token-goes-here
```

_.npmrc_

```
//npm.pkg.github.com/:_authToken=${GITHUB_PACKAGE_REGISTRY_TOKEN}
@spatie:registry=https://npm.pkg.github.com
```

Alternatively, you can use `npm login` to log in to the GitHub Package Registry. Fill in your GitHub credentials, using your Personal Access Token as your password.

```
npm login --registry=https://npm.pkg.github.com --scope=@spatie
```

If you get stuck at any point, have a look at [GitHub's documentation on this](https://docs.github.com/en/free-pro-team@latest/packages/publishing-and-managing-packages/installing-a-package).

## Downloading the packages from GitHub Packages

Now, you can use `npm install --save` or `yarn add` to download the packages you need.

```
yarn add @spatie/media-library-pro-styles @spatie/media-library-pro-vue3-attachment
```

**You will now have to include the `@spatie/` scope when importing the packages**, this is different from examples in the documentation.

```
import { MediaLibraryAttachment } from '@spatie/media-library-pro-vue3-attachment';
```

You can find a list of all the packages on the repository: https://github.com/orgs/spatie/packages?repo_name=laravel-medialibrary-pro.

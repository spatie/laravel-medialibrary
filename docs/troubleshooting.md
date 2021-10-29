---
title: Troubleshooting
weight: 8
---


Here are some common problems and how to solve them.

## Cannot find module '@babel/compat-data/corejs3-shipped-proposals'

This is caused by a compatability issue between a version of `@babel/preset-env` that an older release of Laravel Mix required, and certain versions of node.

### Fix

```
yarn upgrade laravel-mix
```

This will upgrade your installation of `laravel-mix` to a version that requires `@babel/preset-env: ^7.9`. You shouldn't have to change anything else, since this upgrade is a non-breaking change.

## Cannot find name 'describe' / Cannot find name 'test'

This is your TypeScript checker finding methods that it does not have types for.

### Fix

You should add `"include": ["resources/**/*"]` to your tsconfig.json (edit the path to where you manage your JS), or add `"exclude": ["vendor"]`. This doesn't normally happen with other libraries because `node_modules` is excluded from type checking by default.

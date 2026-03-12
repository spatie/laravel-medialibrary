---
title: Troubleshooting
weight: 8
---


Here are some common problems and how to solve them.

## Cannot find name 'describe' / Cannot find name 'test'

This is your TypeScript checker finding methods that it does not have types for.

### Fix

You should add `"include": ["resources/**/*"]` to your tsconfig.json (edit the path to where you manage your JS), or add `"exclude": ["vendor"]`. This doesn't normally happen with other libraries because `node_modules` is excluded from type checking by default.

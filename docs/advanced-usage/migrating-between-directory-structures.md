---
title: Migrating between custom directory structures
weight: 5
---

If you wish to adopt or change your custom directory structure but already have `Media` objects created, 
it is necessary not only to update the `path_generator` config setting, but also to move the 
existing media files (and potentially any associated conversions) to match the new folder structure.

Begin by [creating a custom path generator to meet your requirements](using-a-custom-directory-structure.md), but
do not activate it in your configuration until you are ready to migrate the existing data. 
Once your path generator has been written, the Artisan `media-library:transform-path` command can be used to help 
automate the process of moving existing media to match the new naming scheme.

```
Usage:
  media-library:transform-path [options] [--] <sourceGeneratorClass> <targetGeneratorClass> [<modelType> [<collectionName>]]

Arguments:
  sourceGeneratorClass                Name of the PathGenerator class used to for existing media
  targetGeneratorClass                Name of the PathGenerator class to generate new paths for media
  modelType                           Name of the model to include in processing
  collectionName                      Name of the collection to include in processing

Options:
      --dry-run                       List files that will be moved (without moving them)
      --force                         Force the operation to run when in production
      --rate-limit[=RATE-LIMIT]       Limit the number of items processed per second
      --ignore-missing-source-files   Do not consider missing source files to be an error
      --ignore-existing-target-files  Allow moving/overwriting existing target files
  -v|vv|vvv, --verbose                Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```


If migrating from the library default, use `"Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator"`  
for the `sourceGeneratorClass` parameter.

If `-v` (verbose mode) is turned on, a line will be printed to the console showing the source and target path name for
each file that is moved. If `-vv` (very verbose mode) is turned on, a more detailed report of the planned changes will 
also be presented. This can be useful in combination with `--dry-run` to ensure that the results are as you expect them 
to be.

**NOTE**: This will not necessarily clean up your source disk, which may be left with empty 
directories. Consider running a command such as `find $BASE_DIR -type d -empty -delete` (where `BASE_DIR` 
represents the root of your media storage, e.g. `/var/www/html/storage/app/media`).  (Consider 
using `find $BASE_DIR -type d -empty -print` to verify the impacted directories first!)

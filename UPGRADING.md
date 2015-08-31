#Upgrading

##From v1 to v2
Because v2 is a complete rewrite a simple upgrade path is not available.
If you want to upgrade completely remove the v1 package and follow install instructions of v2.

##From v2 to v3
You can upgrade from v2 to v3 by performing these renames in your model that has media.

- `Spatie\MediaLibrary\HasMediaTrait` has been renamed to `Spatie\MediaLibrary\HasMedia\HasMediaTrait`. 
- `Spatie\MediaLibrary\HasMedia` has been renamed to `Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversions`
- `Spatie\MediaLibrary\HasMediaWithoutConversions` has been renamed to `Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia`

In the config file you should rename the `filesystem`-option to `defaultFilesystem`.

In the db the `temp`-column must be removed. Add these columns:
- disk (varchar, 255)
- custom_properties (text)
You should set the value of disk column in all rows to the name the defaultFilesystem specified in the config file.

Note that this behaviour has changed:
- when calling `getMedia()` without providing a collection name all media will be returned (whereas previously only media
from the default collection would be returned)
- calling `hasMedia()` without a collection name returns true if any given collection contains files (wheres previously
it would only return try if files were present in the default collection)
- the `addMedia`-function has been replaced by a fluent interface. 

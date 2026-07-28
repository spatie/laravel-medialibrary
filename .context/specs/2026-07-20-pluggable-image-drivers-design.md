# Pluggable image drivers design (final)

Supersedes the earlier draft in this file. The design converged through discussion; this records the final, simplified shape.

## Summary

Conversions run through a named image driver. Each driver has its own real image object, and manipulations are written as typed closures against that object. Shipped drivers: `spatie` (default, wraps spatie/image, behavior unchanged), `cloudflare` (Cloudflare transforms the image, we fetch and store the file), and `cloudflare-delivery` (no file is generated; URLs transform at the edge). Custom drivers can be registered.

## Decision record

1. Real objects, not a recorder or neutral vocabulary. A closure is typed against the driver's native image class (`Spatie\Image\Drivers\ImageDriver`, `CloudflareImage`). Unsupported operations do not exist on the type, so errors surface in the IDE and static analysis, not at runtime.
2. `CloudflareImage` is a first class engine object whose native manipulation model is URL parameter accumulation. Its methods are exactly Cloudflare's supported transformation parameters, including capabilities local engines lack (`gravity('face')`, `segment()`, `upscale('generate')`).
3. Closures are wrapped in `SerializableClosure` so queued conversion jobs (which serialize the `ConversionCollection`) keep working.
4. The closure's parameter type infers the driver. Explicit `useImageDriver()` always wins. The `cloudflare-delivery` driver must be selected explicitly (it shares `CloudflareImage` with `cloudflare`).
5. `format()` and `quality()` remain first class conversion settings (not closure calls). Drivers consume them: the spatie driver applies them as manipulations (as today), the Cloudflare drivers map them to URL parameters. This keeps file extension resolution working.
6. The autocompletion story needs no tooling: real classes, real methods, typed closure params. The earlier ide-helper generator idea is dead.

## v1 scope

In: driver manager + config, `Conversion::manipulate(Closure)` + `useImageDriver()`, spatie driver (default, includes legacy fluent manipulations unchanged), Cloudflare storage driver (Mode A), Cloudflare delivery driver (Mode B, virtual conversions), docs, offline tests.

Out (deliberate): Laravel/Intervention driver (documented extension point, fast follow), Cloudflare Images storage/imagedelivery.net (B1), declarative attribute portability to Cloudflare (Cloudflare conversions are closure only), temporary URLs / responsive images / zip export for virtual conversions, a `driver:` argument on the `#[MediaConversion]` attribute.

## Architecture

- `ImageDriverManager` (singleton): resolves named drivers from `media-library.image_drivers`, `extend()` for custom drivers, infers a driver name from a closure's first parameter type, maps the legacy `image_driver` values (`gd`/`imagick`/`vips`) to the spatie driver with that engine.
- Contracts: `MediaImageDriver` (base, `imageClass()`), `GeneratesConversionFiles` (`convert(Media, Conversion, string $file): string`), `ResolvesConversionUrls` (`conversionUrl(Media, Conversion): string`). A conversion whose driver resolves URLs is "virtual": the generation pipeline skips it and `Media::getUrl()` asks the driver instead. `Media::hasGeneratedConversion()` reports true for virtual conversions; `getPath()` throws.
- Cloudflare URL shape: `{zone}/cdn-cgi/image/{params}/{original full url}`. Mode A fetches that URL via Laravel's Http client and stores the bytes as a normal conversion file (original must be reachable by Cloudflare; non image originals are rejected). Delivery mode returns the URL at `getUrl()` time.
- The format parameter maps `jpg`/`pjpg` to `jpeg` and is omitted when Cloudflare cannot output the format (for example `png`, `gif`, which Cloudflare preserves when no format is given).

## Testing

Everything core is offline: `CloudflareImage` param building is pure, Mode A uses `Http::fake()`, delivery mode is string assertions, spatie driver is covered by the existing suite plus closure tests, serialization is a `serialize`/`unserialize` round trip of a job. An optional live smoke test runs only when `CLOUDFLARE_ZONE` is set; CI never needs credentials.

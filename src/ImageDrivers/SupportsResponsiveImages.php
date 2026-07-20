<?php

namespace Spatie\MediaLibrary\ImageDrivers;

/**
 * Marker for file generating drivers whose stored conversions can have
 * responsive images generated from them by the local responsive image
 * generator. A driver that does not implement this (for example the cloudflare
 * driver, which transforms remotely) rejects `withResponsiveImages()`.
 *
 * Virtual drivers build a responsive srcset through
 * {@see ResolvesResponsiveConversionUrls} instead and do not need this.
 */
interface SupportsResponsiveImages extends MediaImageDriver {}

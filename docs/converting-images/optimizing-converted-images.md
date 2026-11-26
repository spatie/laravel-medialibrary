---
title: Optimizing converted images
weight: 3
---

Media Library automatically shrinks converted images by running them through a chain of image optimization binaries.

Optimization only applies to converted images; your original files are left untouched. To skip optimization for a specific conversion, chain `nonOptimized()`:

```php
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->width(368)
        ->height(232)
        ->sharpen(10)
        ->nonOptimized();
}
```

The package uses these optimizers when they are available on your server:

- [JpegOptim](https://github.com/tjko/jpegoptim)
- [Optipng](http://optipng.sourceforge.net/)
- [Pngquant 2](https://pngquant.org/)
- [SVGO](https://github.com/svg/svgo)
- [Gifsicle](http://www.lcdf.org/gifsicle/)
- [cwebp](https://developers.google.com/speed/webp/docs/cwebp)
- [Avifenc](https://github.com/AOMediaCodec/libavif/blob/main/doc/avifenc.1.md)

See [the installation page](/docs/laravel-medialibrary/v11/installation-setup#content-setting-up-optimization-tools) for how to install them.

## Are you a visual learner?

Here's a video that shows how to optimize images.

<iframe width="560" height="315" src="https://www.youtube.com/embed/5Ftv0QnTbwo" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Want to see more videos like this? Check out our [free video course on how to use Laravel Media Library](https://spatie.be/courses/discovering-laravel-media-library).

## Which tools do what?

Media Library picks the right optimizer for each image format automatically. You can customize any of these arguments under `image_optimizers` in `config/media-library.php`.

### JPGs

JPGs run through [JpegOptim](https://github.com/tjko/jpegoptim) with:

- `-m85`: sets the maximum quality to 85%
- `--force`: ensures progressive encoding runs even if the output is slightly larger
- `--strip-all`: strips metadata such as comments and EXIF data
- `--all-progressive`: produces a progressive JPEG

### PNGs

PNGs run through two tools in sequence. First, [Pngquant 2](https://pngquant.org/) (a lossy compressor) runs with `--force`. Then [Optipng](http://optipng.sourceforge.net/) runs with:

- `-i0`: produces a non-interlaced image
- `-o2`: sets optimization level 2
- `-quiet`: suppresses output

### SVGs

SVGs are minified with [SVGO](https://github.com/svg/svgo) using its defaults, except `--disable=cleanupIDs` because stripping IDs can break pages that render multiple SVGs.

Note that SVGO can occasionally break complex SVGs. Sara Soueidan has a [great write-up on SVGO](https://www.sarasoueidan.com/blog/svgo-tools/) if you run into issues.

### GIFs

GIFs are optimized with [Gifsicle](http://www.lcdf.org/gifsicle/) using `-b` (modify in place) and `-O3` (maximum compression).

### WebP

WebP images are optimized with [cwebp](https://developers.google.com/speed/webp/docs/cwebp) using `-m 6 -pass 10 -mt -q 90`.

### AVIF

AVIF images are optimized with [Avifenc](https://github.com/AOMediaCodec/libavif/blob/main/doc/avifenc.1.md) using constant quality (`-a cq-level=23 -a end-usage=q`), SSIM tuning (`-a tune=ssim`), and all available CPU cores (`-j all`).

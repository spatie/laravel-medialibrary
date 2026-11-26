---
title: Attaching media in mails
weight: 9
---

`Media` implements Laravel's [`Attachable` interface](https://laravel.com/docs/mail#attachable-objects), so you can pass a `Media` model straight to `attach()` in a Mailable:

```php
namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class OrderConfirmationMail extends Mailable
{
    public function __construct(public Order $order) {}

    public function build()
    {
        /** @var Media $invoice */
        $invoice = $this->order->getFirstMedia('invoice');

        return $this
            ->view('invoice')
            ->attach($invoice);
    }
}
```

## Attaching a conversion

To attach a specific conversion instead of the original file, call `mailAttachment()` with the conversion name:

```php
namespace App\Mail;

use App\Models\BlogPost;
use Illuminate\Mail\Mailable;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BlogPostThumbnailMail extends Mailable
{
    public function __construct(public BlogPost $blogPost) {}

    public function build()
    {
        /** @var Media $mediaItem */
        $mediaItem = $this->blogPost->getFirstMedia();

        $thumbnailAttachment = $mediaItem->mailAttachment('thumbnail');

        return $this
            ->view('mails.blogpostThumbnail')
            ->attach($thumbnailAttachment);
    }
}
```

## Customizing the attachment

By default, the attachment uses the media item's `file_name` and `mime_type`. To customize how attachments are built, [use a custom `Media` model](/docs/laravel-medialibrary/v11/advanced-usage/using-your-own-model) and override `toMailAttachment()` or `mailAttachment()`.

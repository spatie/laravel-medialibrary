---
title: Attaching media in mails
weight: 9
---

Laravel allows [to attach all sorts of classes](https://laravel.com/docs/10.x/mail#attachable-objects) in mails. The `Media` model implements Laravel's `Attachable` interface, so you can attach `Media` models directly in mails.


```php
namespace App\Mails;

use Illuminate\Mail\Mailable;
use Programic\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Order;

class OrderConfirmationMail extends Mailable
{
    public function __construct(public Order $order)
    {

    }

    public function build()
    {
        /** @var \Programic\MediaLibrary\MediaCollections\Models\Media $invoice */
        $invoice = $this->order->getFirstMedia('invoice')
    
        return $this
            ->view('invoice')
            ->attach($invoice);
    }
}
```

## Using conversions as attachments

You can call  `mailAttachment()` on a `Media` model to get back an `Attachment` that you can use in a Mailable. You can pass the name of a conversion to `mailAttachment()` to get an attachable conversion back.

```php
namespace App\Mails;

use Illuminate\Mail\Mailable;
use Programic\MediaLibrary\MediaCollections\Models\Media;
use App\Models\BlogPost;

class BlogPostThumbnailMail extends Mailable
{
    public function __construct(public BlogPost $blogPost)
    {

    }

    public function build()
    {
        /** @var \Programic\MediaLibrary\MediaCollections\Models\Media $mediaItem */
        $mediaItem = $this->blogPost->getFirstMedia();
        
        // pass the conversion name
        $thumbnailAttachment = $mediaItem->mailAttachment('thumbnail');
    
        return $this
            ->view('mails/blogpostThumbnail')
            ->attach($thumbnailAttachment);
    }
}
```

## Customizing the attachment

By default, the attachment will use the `file_name` and `mime_type` properties to configure Laravel's `Attachment` class. To override how `Attachments` are made, [use a custom media model](/docs/laravel-medialibrary/v11/advanced-usage/using-your-own-model), and override the `toMailAttachment` method.



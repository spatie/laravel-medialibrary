---
title: Introduction
weight: 1
---

[Media Library Pro](http://medialibrary.pro) is a paid add-on package that offers Blade, Vue, and React components to upload files to your application.

**Media Library Pro hasn't launched yet. We will release the package mid-november 2020. Subscribe at [Media Library Pro](http://medialibrary.pro) to get notified the moment it is available.** 

Media Library Pro ships with two components for every environment: an attachment component, and a collection component.

The attachment component can upload one or more files with little or no extra information. It's is a lightweight solution for small bits of UI like avatar fields or message attachments.

![Screenshot of the attachment component](/docs/laravel-medialibrary/v9/images/pro/attachment.png)

The collection component can upload multiple files with custom properties. Use the collection component shines when you need to manage media, for example in admin panels.

![Screenshot of the attachment component](/docs/laravel-medialibrary/v9/images/pro/collection.png)

If none of those fit the bill, Media Library Pro supplies you with a number helpers to build your own components.

## Dive in

All components upload media to the server with the same API. Before you dive into the frontend, read our server guide.

[Processing uploads on the server](processing-uploads-on-the-server)

Next, choose your own journey. We have written extensive guides for all three flavours. Be sure to first follow [the base installation instructions](/docs/laravel-medialibrary/v9/installation-setup) and [pro installation instructions](/docs/laravel-medialibrary/v9/handling-uploads-with-media-library-pro/installation).

### Blade

- [Handling uploads with Blade](/docs/laravel-medialibrary/v9/handling-uploads-with-media-library-pro/handling-uploads-with-blade)

### Vue

- [Handling uploads with Vue](handling-uploads-with-vue)
- [Creating custom Vue components](creating-custom-vue-components)

### React

- [Handling uploads with React](handling-uploads-with-react) <br>
- [Creating custom React components](creating-custom-react-components)

## Demo application

We've created a demo application in which all components are installed. This way you'll have a full example on how to use components in your project. 

You'll find the code of the demo application in [this repository on GitHub](https://github.com/spatie/laravel-medialibrary-pro-app). In order to `composer install` on that project, you'll need to have [a license](/docs/laravel-medialibrary/v9/handling-uploads-with-media-library-pro/installation#getting-a-license).

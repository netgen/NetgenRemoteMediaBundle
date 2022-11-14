# Cloudinary webhook notifications

Remote Media stores all the metadata for used resources internally in the database, to avoid querying the remote API for displaying the resource on frontend for the sake of performance and prevention of breaking remote API limits.

This has one major drawback: if you update the resource directly on the cloud, or from another system using the same integration and same cloud account, your current system won't be aware of that change, and you will be using deprecated data which might result with wrong metadata (such as tags, size, alt text etc.) or even worse, 404 when you try to use the resource.

Furthermore, Cloudinary integration caches all the requests towards Cloudinary API so if you change something directly in Cloudinary interface or upload new file, it won't be visible in the Remote Media interface.

Cloudinary has an option to notify your app about any changes that just happened on your cloud, through [Webhook notifications](https://cloudinary.com/documentation/notifications). This bundle contains an implementation for this through a controller which can be called by Cloudinary. This will automatically 

## Configure callback in Cloudinary interface

First you need to find out the URL in your app which will receive those notifications. If you haven't used any kind of prefix for the Remote Media routing (check [instructions](../INSTALL.md#add-routing) for adding routing), the link is:

```
http(s)://[YOUR_APP_DOMAIN]/ngremotemedia/callback/cloudinary/notify
```

Now you have to go to Cloudinary interface under `Settings -> Upload` and paste this URL to `Notification URL` field.

## Problem when using same cloud for multiple projects

Unfortunately, Cloudinary has only one `Notification URL` field, so it's not possible to specify multiple URLs that will receive the notification. If you use the same cloud on multiple instances that run Remote Media (eg. multiple sites using the same cloud account or one site having multiple platforms, such as eZ and Sylius in parallel both running Remote Media), this will be problematic as only one instance will be notified about changes. And if you update a resource on this instance, all others won't get the change.

One way to solve this would be to create some kind of proxy which will receive the main notification from Cloudinary and then dispatch the same request to a list of configured URLs.

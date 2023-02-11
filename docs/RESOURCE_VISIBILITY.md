# Resource visibility

This bundle has an implementation for different visibility types in a way that you can choose the visibility when uploading the file (eg. `public` or `protected`), and this one won't be publicly available on the cloud, and you won't be able to use it without authenticating it first via token, to get the signed URL.

**Note:** used provider has to support this!

## Provider support

There's a method to check if your provider supports this: `Netgen\RemoteMedia\API\ProviderInterface::supportsProtectedResources()`. Also, there's a method to get the list of supported visibilities by this provider: `Netgen\RemoteMedia\API\ProviderInterface::getSupportedVisibilities()`. If you call a method related to protected resources while your provider doesn't support it, you will receive a `Netgen\RemoteMedia\Exception\NotSupportedException`.

### Cloudinary support

Cloudinary has a support for this but token based authentication, which this bundle uses, is a premium feature! You need to have a premium plan and request this feature manually from Cloudinary. They should provide an encryption token which you should properly [configure](INSTALL.md##auth-token-for-protected-resources-cloudinary-premium).

## Upload

When uploading new resources, `Netgen\RemoteMedia\API\Upload\ResourceStruct` object has `$visibility` argument, where you can provide desired visibility. The list of available visibilities is available at the `Netgen\RemoteMedia\API\Values\RemoteResource` class via constants. But you should be careful about visibilities supported by the currently used provider!

Example for uploading eg. protected resource:

```php
/** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
$file = $request->files->get('file');

$fileStruct = FileStruct::fromUploadedFile($file);

$resourceStruct = new ResourceStruct(
    $fileStruct,
    'auto',
    null,
    RemoteResource::VISIBILITY_PROTECTED,
);

$provider->upload($resourceStruct);
```

## Using protected resources

By default, the protected resource will look like any other resource, and it will contain an URL but this URL will not work! In order to use it (display image/video, or download file), you have to authenticate it first.

### Usage in code

There are two methods available in the interface to authenticate resource itself or it's variation:

* `Netgen\RemoteMedia\API\ProviderInterface::authenticateRemoteResource(RemoteResource $resource, AuthToken $token)`
* `Netgen\RemoteMedia\API\ProviderInterface::authenticateRemoteResourceVariation(RemoteResourceVariation $variation, AuthToken $token)`

Both methods accept `Netgen\RemoteMedia\API\Values\AuthToken` object through which you can decide for who and for how long the link will be available. Current implementation supports setting:

* duration (in seconds)
* start time
* expiration time
* IP address

#### Example

If you have an image variation for a protected resource that you want to show to the user with IP address `172.0.0.1` in the next 7 days, you can do it like this:

```php
/** @var \Netgen\RemoteMedia\API\ProviderInterface $provider */
$location = $provider->loadLocation(5);
$variation = $provider->buildVariation($location, 'article', 'hero_image');

/** @var \Netgen\RemoteMedia\API\Values\AuthToken $authToken */
$authToken = AuthToken::fromDuration(7*24*60*60);
$authToken->setIpAddress('127.0.0.1');

/** @var \Netgen\RemoteMedia\API\Values\AuthenticatedRemoteResource $authenticatedResource */
$authenticatedResource = $provider->authenticateRemoteResourceVariation($variation, $authToken);

if ($authenticatedResource->isValid()) {
    $url = $authenticatedResource->getUrl();
}
```

## Symfony Form Type

By default, Symfony form type will use all visibilities supported by the current provider. If there's more than one, during upload you will be able to select the desired one (with first one being preselected automatically). If there's only one, you won't see a selector and this one will be used automatically. Also, by default it will show all available resources with all visibilities.

It's possible to configure available visibilities through [form options](FORM.md#allowed_visibilities).

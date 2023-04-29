# Resource visibility

This bundle has an implementation for different visibility types in a way that you can choose the visibility when uploading the file (eg. `public` or `protected`), and this one won't be publicly available on the cloud, and you won't be able to use it without authenticating it first via token, to get the signed URL.

**Note:** used provider has to support this!

## Provider support

There's a method to check if your provider supports this: `Netgen\RemoteMedia\API\ProviderInterface::supportsProtectedResources()`.

Also, there's a method to get the list of supported visibilities by this provider: `Netgen\RemoteMedia\API\ProviderInterface::getSupportedVisibilities()`.

If you call a method related to protected resources while your provider doesn't support it, you will receive a `Netgen\RemoteMedia\Exception\NotSupportedException`.

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

There are two methods available in the interface to authenticate resource or location:

```php
public function authenticateRemoteResource(RemoteResource $resource, AuthToken $token): AuthenticatedRemoteResource;

public function authenticateRemoteResourceLocation(RemoteResourceLocation $location, AuthToken $token): RemoteResourceLocation;
```

Both methods accept `Netgen\RemoteMedia\API\Values\AuthToken` object through which you can decide for who and for how long the link will be available. Current implementation supports setting:

* duration (in seconds)
* start time
* expiration time
* IP address

If the authentication is successful, you will receive a `Netgen\RemoteMedia\API\Values\AuthenticatedRemoteResource` object. This one extends the original `Netgen\RemoteMedia\API\Values\RemoteResource` but it contains the correct URL, and additionally it contains the token, so you can check if it's valid with:

```php
$authenticatedRemoteResource->isValid($ipAddress);
```

If you're authenticating location, you will receive a new location that will have authenticated resource.

At last, you can use authenticated resources and locations as regular ones, and it works with all other methods. If you want to create a variation, or a video thumbnail, or HTML tag, you can pass authenticated resource or location to the corresponding method, and it will automatically use authenticated URL in the output.

#### Example

If you have an image variation for a protected resource that you want to show to the user with IP address `172.0.0.1` in the next 7 days, you can do it like this:

```php
/** @var \Netgen\RemoteMedia\API\ProviderInterface $provider */
$location = $provider->loadLocation(5);

/** @var \Netgen\RemoteMedia\API\Values\AuthToken $authToken */
$authToken = AuthToken::fromDuration(7*24*60*60);
$authToken->setIpAddress('127.0.0.1');

/** @var \Netgen\RemoteMedia\API\Values\AuthenticatedRemoteResource $authenticatedResource */
$authenticatedLocation = $provider->authenticateRemoteResourceLocation($location, $authToken);

$variation = $provider->buildVariation($authenticatedLocation, 'article', 'hero_image');

if ($variation->getRemoteResource()->isValid('127.0.0.1')) {
    $url = $variation->getUrl();
}
```

### Usage in Twig

There are two Twig functions available to authenticate a resource or location:

* `ngrm_authenticate_remote_resource` (receives resource and duration in seconds)
* `ngrm_authenticate_remote_resource_location` (receives variation and duration in seconds)

Those methods are returning `Netgen\RemoteMedia\API\Values\AuthenticatedRemoteResource` object or a location which contains this object, respectively.

#### Example

```html
{% set location = entity.remoteResourceLocation %}
{% set authenticated_location = ngrm_authenticate_remote_resource_location(location, 60) %}
{% set variation = ngrm_remote_resource_variation(authenticated_location, 'product', 'cover') %}


<img src="{{ variation.url }}" alt="location.remoteResource.altText"/>

{# OR #}

{{ ngrm_remote_resource_variation_html_tag(authenticated_location, 'product', 'cover') }}
```

Of course, you can use this in combination with Symfony Security:

```html
{% set location = entity.remoteResourceLocation %}

{% if location.remoteResource.public %}
    {{ ngrm_remote_resource_variation_html_tag(location, 'product', 'cover') }}
{% elseif is_granted('ROLE_MEDIA') %}
    {% set authenticated_location = ngrm_authenticate_remote_resource_location(location, 60) %}

    {{ ngrm_remote_resource_variation_html_tag(authenticated_location, 'product', 'cover') }}
{% endif %}
```

## Symfony Form Type

By default, Symfony form type will use all visibilities supported by the current provider. If there's more than one, during upload you will be able to select the desired one (with first one being preselected automatically). If there's only one, you won't see a selector and this one will be used automatically. Also, by default it will show all available resources with all visibilities.

It's possible to configure available visibilities through [form options](FORM.md#allowed_visibilities).

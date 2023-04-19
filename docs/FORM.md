# Adding Remote Media field in Symfony form

If you want to be able to upload, select, edit or remove remote resources inside a form (eg. in your CMS system), this bundle contains Symfony form type implementation, so it's possible to add it to any existing Symfony form and have the whole interface available there.

## How it works

You will get the whole interface written in Vue.js where you can browse existing resources (with search and filtering by type, tags, folders etc.), upload new resources (and create folders if needed) and adjust cropping for all available variations.

When you add a resource to your entity, this will be only a relation to objects stored in internal tables. All the data needed to display the resource on frontend will be stored internally in the database, so that it doesn't need any requests towards the remote API (to improve performance and prevent breaking API limits).

**Note:** if using Cloudinary, you might want to check the [Remote Callback](Cloudinary/REMOTE_CALLBACK.md) configuration to get your locally stored resources automatically updated when the resource gets changed on Cloudinary (either directly through interface or from other system using the same account and resource).

## Add relation to your entity

All the data needed for Remote Media to work will be stored in its own internal tables in the database, by using `Netgen\RemoteMedia\API\Values\RemoteResource` and `Netgen\RemoteMedia\API\Values\RemoteResourceLoation` entities so your entity will need to have a relation to `Netgen\RemoteMedia\API\Values\RemoteResourceLocation` entity.

**Important:** Those external objects won't be persisted automatically to the database, so you have to use proper cascade configuration when adding it to your entity to allow Doctrine to properly handle persisting or removing those.

Example:

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;

#[ORM\Entity]
class MyEntity
{
    #[ORM\OneToOne(targetEntity: RemoteResourceLocation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?RemoteResourceLocation $remoteResourceLocation = null;

    public function getRemoteResourceLocation(): ?RemoteResourceLocation
    {
        return $this->remoteResourceLocation;
    }

    public function setRemoteResourceLocation(?RemoteResourceLocation $remoteResourceLocation): void
    {
        $this->remoteResourceLocation = $remoteResourceLocation;
    }    
}
```

The above example will make sure that, when you select a new resource in the form, Doctrine automatically create both `RemoteResource` and `RemoteResourceLocation` objects in the database and link the location to your entity. Also, if you remove the value for this attribute, it will automatically delete location from the database.

The reason why we're using `OneToOne` relation here is because the idea is that every usage of a remote resource has it's own location. Things such as cropping settings or watermark text are stored in a location, and you want to have different cropping settings for the same eg. image used on different places.

## Add field to your form

Now when we have the entity configured properly, it's time to add the form field:

```php
<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Netgen\RemoteMedia\Form\Type\RemoteMediaType;

class MyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('remote_resource_location', RemoteMediaType::class);
    }
}
```

### Form options

There are a few options available on form type that you can use to eg. limit browse/upload to specific types or folders:

#### `variation_group`

If provided, only variations from this group + default variations will be available in the cropping interface. By default, it's set to `default` variation group.

#### `allowed_visibilities`

You can specify an array of allowed visibilities (eg. `public`, `private` or `protected` etc.) which will limit the available resources only to those types when browsing. Also, you will be able to select only those visibilities in the visibility filter. If only one visibility is provided, the filter won't show at all.

This will also affect the upload functionality. You will be able to select only specified visibilities when uploading, and if only one is provided, the selector won't be shown at all.

#### `allowed_types`

You can specify an array of allowed types (eg. `image`, `video`, `document` etc.) which will limit the available resources only to those types when browsing. Also, you will be able to select only those types in the type filter. If only one type is provided, the filter won't show at all. You will still be able to upload different types, but they won't show, and you won't be able to use them in the form.

#### `allowed_tags`

You can specify an array of allowed tags which will limit the available resources only to those that contain those specific tags when browsing. Also, you will be able to select only those tags in the tag filter. If only one tag is provided, the filter won't show at all. You also won't be able to add any other tags to a resource.

#### `parent_folder`

You can specify a parent folder to limit the available resources and upload only to a specific subtree. You will be able to see only this specified folder and all folders below it, create folders below it and upload files to this one folder or any folder below it. You won't be able to see any folders above it, or it's siblings.

It accepts either `Netgen\RemoteMedia\API\Values\Folder` object or path to a folder (eg. `media/images`).

#### `folder`

You can specify a single folder to limit the available resources and upload only to this one specific folder. You will be able to see only resources inside this folder and upload new resources to this folder, but you won't see it's subfolders or be able to create a new subfolder inside it.

It accepts either `Netgen\RemoteMedia\API\Values\Folder` object or path to a folder (eg. `media/images`).

#### `upload_context`

You can specify an array of (key, value) pairs that will be added to the file as a context during upload. This context can be later used for search/filtering. See the example below. 

#### `location_source`

Every location has a string where you can put a descriptive info where this location will be used (eg. `product_image`). When adding resources through this form, by default it will use `form_[FORM_NAME]` so if you have eg. this code: `$builder->add('remote_resource_location' RemoteMediaType::class);`, all locations added through this form will have source `form_remote_resource_location`.

This parameter enables you to override the source text.

### Example

Let's say that you have a form for digital products in a webshop and you want to limit editors to be able to upload only protected files inside a specific folder. You want to also add some context.

```php
<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Netgen\RemoteMedia\Form\Type\RemoteMediaType;

class MyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'remote_resource_location',
            RemoteMediaType::class,
            [
                'allowed_visibilities' => ['protected'],
                'folder' => ['media/products/files'],
                'upload_context' => [
                    'sylius_type' => 'product',
                    'sylius_code' => $this->getProductCode(),
                    'sylius_form_field_name' => 'remote_resource_location',
                    'source' => 'user_upload',
                ],
            ],
        );
    }
}
```

This will show only protected files in folder `media/products/files` and you will be able to upload only to this folder.

## Add CSS and JS files for the interface

Form field uses a custom interface implemented in Vue.js, so we need to include Javascript and CSS files needed for the interface. Ideally you want to include this only when you need it (eg. on create/edit pages for the entity where you have Remote Resource field).

### Stylesheets

```html
<link rel="stylesheet" href="{{ asset('/bundles/netgenremotemedia/css/remotemedia.css') }}"/>
<link rel="stylesheet" href="{{ asset('/bundles/netgenremotemedia/css/remotemedia-vendors.css') }}"/>
```

### Javascripts

```html
<script src="{{ asset('/bundles/netgenremotemedia/js/remotemedia.js') }}"></script>
<script src="{{ asset('/bundles/netgenremotemedia/js/remotemedia-vendors.js') }}"></script>
```

## Adjust the look of your form

You might need to adjust the CSS a little so that it matches the rest of your form and that the look suits your needs.

Also, although the Vue.js app interface uses it's own design based on Bootstrap (which is bundled and not needed in your project), it might happen that the interface's modal doesn't show correctly or doesn't look good (due to conflicts with the rest of your app) so you might want to adjust it a little.

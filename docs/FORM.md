# Adding Remote Media field in Symfony form

If you want to be able to upload, select, edit or remove remote resources inside a form (eg. in your CMS system), this bundle contains Symfony form type implementation, so it's possible to add it to any existing Symfony form and have the whole interface available there.

## How it works

You will get the whole interface written in Vue.js where you can browse existing resources (with search and filtering by type, tags, folders etc.), upload new resources (and create folders if needed) and adjust cropping for all available variations.

When you add a resource to your entity, this will be only a relation to objects stored in internal tables. All the data needed to display the resource on frontend will be stored internally in the database, so that it doesn't need any requests towards the remote API (to improve performance and prevent breaking API limits).

**Note:** if using Cloudinary, you might want to check the [Remote Callback](Cloudinary/REMOTE_CALLBACK.md) configuration to get your locally stored resources automatically updated when the resource gets changed on Cloudinary (either directly through interface or from other system using the same account and resource).

## Add relation to your entity

All the data needed for Remote Media to work will be stored in its own internal tables in the database, by using `Netgen\RemoteMedia\API\Values\RemoteResource` and `Netgen\RemoteMedia\API\Values\RemoteResourceLocation` entities so your entity will need to have a relation to `Netgen\RemoteMedia\API\Values\RemoteResourceLocation` entity.

The form field always works with a collection of locations, also when it accepts only one resource (see [`upload_limit`](#upload_limit)). So the relation is a to-many relation, even for a single image.

**Important:** Those external objects won't be persisted automatically to the database, so you have to use proper cascade configuration when adding it to your entity to allow Doctrine to properly handle persisting or removing those.

Example:

```php
<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;

#[ORM\Entity]
class MyEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * @var Collection<int, RemoteResourceLocation>
     */
    #[ORM\ManyToMany(targetEntity: RemoteResourceLocation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'my_entity_image')]
    #[ORM\JoinColumn(name: 'my_entity_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'remote_resource_location_id', referencedColumnName: 'id', unique: true, onDelete: 'CASCADE')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, RemoteResourceLocation>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(RemoteResourceLocation $image): void
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
        }
    }

    public function removeImage(RemoteResourceLocation $image): void
    {
        $this->images->removeElement($image);
    }
}
```

The above example will make sure that, when you select a new resource in the form, Doctrine automatically creates both `RemoteResource` and `RemoteResourceLocation` objects in the database and links the location to your entity. When you remove a resource in the form, its location is deleted from the database. The resource itself stays.

Some details of the example matter:

* The form returns a new collection on every submit. With `addImage()` and `removeImage()`, Symfony changes the collection that Doctrine already tracks, item by item. Only then does `orphanRemoval` delete the location of a removed item. A plain setter that replaces the whole collection leaves the removed location in the `ngrm_remote_resource_location` table.
* The form stores the order on the locations, in the `position` column. `#[ORM\OrderBy(['position' => 'ASC'])]` loads them in that order. `ManyToMany` itself does not keep the order.
* `unique: true` on the inverse join column makes sure that a location belongs to one entity only. The position is stored on the location, so a location can't be part of two ordered collections.
* `cascade: ['persist', 'remove']` stores new locations, and deletes the locations when you delete the entity.

The reason why every entity has its own locations is that every usage of a remote resource has its own location. Things such as cropping settings or watermark text are stored in a location, and you want to have different cropping settings for the same eg. image used on different places.

## Alt text and caption overrides

Alt text and caption entered in the interface are stored on the resource in the cloud, so changing them updates every location which uses that resource. Each location can additionally have its own alt text and caption override (`altTextOverride` and `captionOverride`), stored only on that location and never sent to the cloud. An empty override means that the value from the cloud is used.

The form field and the CKEditor 5 plugin show the inputs for the cloud values and for the overrides next to each other, for every item. An emptied override input removes the override.

`RemoteResourceLocation::getAltText()` and `RemoteResourceLocation::getCaption()` return the override when it's set and the value from the cloud otherwise, so use them (eg. `location.caption` in TWIG) instead of the ones on the resource when rendering a location:

```twig
{{ ngrm_remote_resource_variation_html_tag(location, 'default', 'wide') }}
{{ ngrm_remote_resource_location_html_tag(location) }}

{% if location.caption is not empty %}
    <figcaption>{{ location.caption }}</figcaption>
{% endif %}
```

Both TWIG functions render the overrides as `alt` and `title` attributes of images and video thumbnails, unless those attributes are explicitly provided. `ngrm_remote_resource_html_tag` receives only the resource, so it always uses the values from the cloud.

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
        $builder->add('images', RemoteMediaType::class);
    }
}
```

The field reads and writes `MyEntity::$images` and lets editors add any number of resources. Use the `upload_limit` option to limit that, see below.

### Form options

There are a few options available on form type that you can use to eg. limit browse/upload to specific types or folders:

#### `variation_group`

If provided, only variations from this group + default variations will be available in the cropping interface. By default, it's set to `default` variation group.

#### `allowed_visibilities`

You can specify an array of allowed visibilities (eg. `public` or `protected` etc.) which will limit the available resources only to those types when browsing. Also, you will be able to select only those visibilities in the visibility filter. If only one visibility is provided, the filter won't show at all.

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

Every location has a string where you can put a descriptive info where this location will be used (eg. `product_image`). When adding resources through this form, by default it will use `form_[FORM_NAME]` so if you have eg. this code: `$builder->add('images', RemoteMediaType::class);`, all locations added through this form will have source `form_images`.

This parameter enables you to override the source text.

#### `disable_upload`

This parameter enables you to disable the upload, which means that users will be able only to select one of the existing resources.

#### `hide_filename`

This parameter enables filename hiding; instead of original filename, it will use file's MD5 hash as a public ID. Original filename will be stored in the context though and can be used, if needed.

#### `upload_limit`

The maximum number of resources the field accepts. It's either `null` (default, unlimited) or a positive integer.

The field data is always a Doctrine `ArrayCollection` of `RemoteResourceLocation` objects, in the order chosen in the interface. That doesn't depend on the limit: with `'upload_limit' => 1` the collection has 0 or 1 items. So changing the limit later doesn't change the entity mapping or the stored data.

The limit changes the interface: with `'upload_limit' => 1`, the picker selects one resource and a new upload or selection replaces the current one. The limit is also checked on the server. If a submission contains more resources than the limit, the form is invalid and shows the `ngrm.form.upload_limit_exceeded` message from the `validators` translation domain.

#### How the field is submitted

The widget submits the whole selection as one hidden JSON field, `collectionPayload`. It's a list of objects, one per resource, in the chosen order:

```json
[
    {"locationId": 12, "remoteId": "upload|image|media/a.jpg", "altTextOverride": "A dog"},
    {"remoteId": "upload|image|media/b.jpg", "cropSettings": "{\"wide\":{\"x\":0,\"y\":0,\"w\":800,\"h\":400}}"}
]
```

* An entry without `locationId` creates a new location. An entry with the `locationId` of a location from the field's current value updates that location. Any other `locationId` is ignored and a new location is created.
* Entry fields are `locationId`, `remoteId`, `type`, `altText`, `caption`, `altTextOverride`, `captionOverride`, `watermarkText`, `tags` (a list of strings), `cropSettings` (a JSON string) and `source`. A field with the wrong type makes the form invalid.
* `altText`, `caption` and `tags` are the values of the resource in the cloud. If they differ from the stored ones, the resource is updated in the cloud. An empty or missing `altText`, `caption`, `altTextOverride` or `captionOverride` is stored as `null`, so code which submits the field must send the current `altText`, `caption` and `tags` to keep them. Missing `tags` are read as an empty list.
* If the same `locationId` is sent twice, only the first entry is used.
* If `collectionPayload` is not submitted at all (eg. the widget didn't load), the field keeps its current value. Only an empty list, `[]`, clears the field.
* The form theme renders `collectionPayload` with the current value, so a form submitted without the widget doesn't change the field.
* After an invalid submission, the widget shows the submitted selection again, not the stored one.

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
            'files',
            RemoteMediaType::class,
            [
                'upload_limit' => 1,
                'allowed_visibilities' => ['protected'],
                'folder' => 'media/products/files',
                'upload_context' => [
                    'sylius_type' => 'product',
                    'sylius_code' => $this->getProductCode(),
                    'sylius_form_field_name' => 'files',
                    'source' => 'user_upload',
                ],
            ],
        );
    }
}
```

This will show only protected files in folder `media/products/files` and you will be able to upload only to this folder. The field accepts one file, and `files` is a to-many relation as in the entity example above.

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

`# Adding Remote Media field in Symfony form

If you want to be able to upload, select, edit or remove remote resources inside a form (eg. in your CMS system), this bundle contains Symfony form type implementation, so it's possible to add it to any existing Symfony form and have the whole interface available there.

## How it works

You will get the whole interface written in Vue.js where you can browse existing resources (with search and filtering by type, tags, folders etc.), upload new resources (and create folders if needed) and adjust cropping for all available variations.

When you add a resource to your entity, this will be only a relation to objects stored in internal tables which will be automatically generated for you, so you don't have to do any manual work in your controller, on form submission.

All the data needed to display the resource on frontend will be stored internally in the database, so that it doesn't need any requests towards the remote API (to improve performance and prevent breaking API limits).

**Note:** if using Cloudinary, you might want to check the [Remote Callback](Cloudinary/REMOTE_CALLBACK.md) configuration to get your locally stored resources automatically updated when the resource gets changed on Cloudinary (either directly through interface or from other system using the same account and resource).

## Add relation to your entity

All the data needed for Remote Media to work will be stored in its own internal tables in the database, by using `Netgen\RemoteMedia\API\Values\RemoteResource` and `Netgen\RemoteMedia\API\Values\RemoteResourceLoation` entities so your entity will need to have a relation to `Netgen\RemoteMedia\API\Values\RemoteResourceLocation` entity, for example:

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;

/**
 * @ORM\Entity
 */
class MyEntity
{
    /**
     * @ORM\OneToOne(targetEntity="Netgen\RemoteMedia\API\Values\RemoteResourceLocation")
     */
    private ?RemoteResourceLocation $remoteResource = null;

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

The reason why we're using `OneToOne` relation here is because the idea is that every usage of a remote resource has it's own location. Cropping settings are stored in a location and you want to have different cropping settings for the same eg. image used on different places.

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

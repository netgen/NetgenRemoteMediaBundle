<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\Type;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_intersect;
use function array_replace;
use function is_iterable;
use function is_string;

/**
 * Shared scaffolding for the single-resource RemoteMediaType and the
 * ordered multi-resource RemoteMediaCollectionType.
 */
abstract class AbstractRemoteMediaType extends AbstractType
{
    public function __construct(
        protected DataTransformerInterface $transformer,
        protected ProviderInterface $provider,
    ) {}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'variation_group' => 'default',
            'allowed_variations' => [],
            'allowed_visibilities' => [],
            'allowed_types' => [],
            'allowed_tags' => [],
            'parent_folder' => null,
            'folder' => null,
            'upload_context' => [],
            'location_source' => null,
            'disable_upload' => false,
            'hide_filename' => false,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('locationId', HiddenType::class, ['required' => false])
            ->add('remoteId', HiddenType::class)
            ->add('type', HiddenType::class)
            ->add('altText', HiddenType::class, ['required' => false])
            ->add('caption', HiddenType::class, ['required' => false])
            ->add('watermarkText', HiddenType::class, ['required' => false])
            ->add(
                'tags',
                CollectionType::class,
                [
                    'required' => false,
                    'entry_type' => HiddenType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                ],
            )
            ->add('cropSettings', HiddenType::class)
            ->add(
                'source',
                HiddenType::class,
                [
                    'data' => $options['location_source'] ?? 'form_' . $builder->getName(),
                ],
            );

        $builder->addModelTransformer($this->transformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $parentFolder = null;
        if ($options['parent_folder'] !== null) {
            $parentFolder = ($options['parent_folder'] instanceof Folder)
                ? $options['parent_folder']
                : Folder::fromPath($options['parent_folder']);
        }

        $folder = null;
        if ($options['folder'] !== null) {
            $folder = ($options['folder'] instanceof Folder)
                ? $options['folder']
                : Folder::fromPath($options['folder']);
        }

        $uploadContext = $options['upload_context'];
        foreach ($uploadContext as $key => $value) {
            if (!is_string($key) || !$key) {
                unset($uploadContext[$key]);
            }
        }

        $view->vars = array_replace($view->vars, [
            'variation_group' => $options['variation_group'] ?? 'default',
            'allowed_variations' => $options['allowed_variations'] ?? [],
            'allowed_visibilities' => array_intersect($options['allowed_visibilities'], $this->provider->getSupportedVisibilities()),
            'allowed_types' => array_intersect($options['allowed_types'], $this->provider->getSupportedTypes()),
            'allowed_tags' => array_intersect($options['allowed_tags'], $this->provider->listTags()),
            'parent_folder' => $parentFolder instanceof Folder
                ? ['id' => $parentFolder->getPath(), 'label' => $parentFolder->getName()]
                : null,
            'folder' => $folder instanceof Folder
                ? ['id' => $folder->getPath(), 'label' => $folder->getName()]
                : null,
            'upload_context' => $uploadContext,
            'disable_upload' => $options['disable_upload'] ?? false,
            'hide_filename' => $options['hide_filename'] ?? false,
            'remote_media_locations' => static::extractRemoteMediaLocations($form->getData()),
        ]);
    }

    /**
     * @return array<int, RemoteResourceLocation>
     */
    protected static function extractRemoteMediaLocations(mixed $data): array
    {
        if ($data instanceof RemoteResourceLocation) {
            return [$data];
        }

        if (!is_iterable($data)) {
            return [];
        }

        $locations = [];
        foreach ($data as $item) {
            if ($item instanceof RemoteResourceLocation) {
                $locations[] = $item;
            }
        }

        return $locations;
    }
}

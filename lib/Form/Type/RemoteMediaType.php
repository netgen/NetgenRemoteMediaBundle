<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\Type;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaCollectionEntryExtractor;
use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaCollectionTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_filter;
use function array_flip;
use function array_intersect;
use function array_key_exists;
use function array_map;
use function array_replace;
use function is_array;
use function is_iterable;
use function is_scalar;
use function is_string;
use function json_encode;

use const JSON_INVALID_UTF8_SUBSTITUTE;

/**
 * Remote media form type.
 *
 * The `upload_limit` option decides the shape of the model data:
 *  - 1: a single RemoteResourceLocation, or null;
 *  - null (default, unlimited) or any other positive integer: an ordered
 *    Doctrine ArrayCollection of RemoteResourceLocation objects (possibly
 *    empty). The limit is enforced server-side during reverse transformation.
 */
final class RemoteMediaType extends AbstractType
{
    /**
     * @param DataTransformerInterface<mixed, mixed> $transformer
     */
    public function __construct(
        private DataTransformerInterface $transformer,
        private ProviderInterface $provider,
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
            'upload_limit' => null,
        ]);

        $resolver->setAllowedTypes('upload_limit', ['int', 'null']);
        $resolver->setAllowedValues('upload_limit', static fn ($value) => $value === null || $value >= 0);
        $resolver->setNormalizer('upload_limit', static function (Options $options, $value) {
            // Legacy convention: 0 means unlimited.
            return $value === 0 ? null : $value;
        });
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

        if (self::isCollection($options)) {
            $this->buildCollectionForm($builder, $options);

            return;
        }

        $builder->addModelTransformer($this->transformer);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event): void {
            $data = $event->getData();
            if (!is_array($data)) {
                return;
            }

            // Defense-in-depth: if a client submits collection-shaped (indexed)
            // data to a single field, collapse it to the first entry instead of
            // leaking arrays into the scalar HiddenType children.
            $entries = RemoteMediaCollectionEntryExtractor::fromRootIndexed($data);
            if ($entries === []) {
                $entries = RemoteMediaCollectionEntryExtractor::fromFieldIndexed($data);
            }

            $data = RemoteMediaCollectionEntryExtractor::stripNumericKeys($data);

            if ($entries !== []) {
                $data = self::copyEntryToRootFields($data, $entries[0]);
            }

            // Only reuse a locationId that belongs to this form's pre-set data;
            // a foreign id (even with a matching remoteId) creates a fresh
            // location instead of re-pointing another entity's placement.
            if (array_key_exists('locationId', $data) && is_scalar($data['locationId']) && $data['locationId'] !== '') {
                $allowedIds = array_flip(self::extractExistingLocationIds($event->getForm()->getData()));
                if (!array_key_exists((string) $data['locationId'], $allowedIds)) {
                    $data['locationId'] = null;
                }
            }

            $event->setData($data);
        });
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
            'upload_limit' => $options['upload_limit'],
            'is_collection' => self::isCollection($options),
            'remote_media_locations' => self::extractRemoteMediaLocations($form->getData()),
        ]);
    }

    /**
     * @param array<string, mixed> $options
     */
    private static function isCollection(array $options): bool
    {
        return $options['upload_limit'] !== 1;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function buildCollectionForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'uploadLimit',
                HiddenType::class,
                [
                    'required' => false,
                    'data' => (string) ($options['upload_limit'] ?? 0),
                ],
            )
            ->add(
                'collectionPayload',
                HiddenType::class,
                [
                    'required' => false,
                ],
            );

        $builder->addModelTransformer(new RemoteMediaCollectionTransformer($this->transformer));

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) use ($options): void {
            $data = $event->getData();
            if (!is_array($data)) {
                return;
            }

            // The limit is server state; never trust the submitted value.
            $data['uploadLimit'] = (string) ($options['upload_limit'] ?? 0);

            $payloadIsSubmitted = array_key_exists('collectionPayload', $data);

            if ($payloadIsSubmitted) {
                try {
                    $entries = RemoteMediaCollectionEntryExtractor::fromPayload($data);
                } catch (TransformationFailedException) {
                    $entries = null;
                }
            } else {
                $entries = RemoteMediaCollectionEntryExtractor::fromRootIndexed($data);
                if ($entries === []) {
                    $entries = RemoteMediaCollectionEntryExtractor::fromFieldIndexed($data);
                }
            }

            $data = RemoteMediaCollectionEntryExtractor::stripNumericKeys($data);

            if (is_array($entries)) {
                $entries = self::filterSubmittedLocationIds($entries, $event->getForm()->getData());

                $encodedEntries = json_encode($entries, JSON_INVALID_UTF8_SUBSTITUTE);
                if ($encodedEntries !== false) {
                    $data['collectionPayload'] = $encodedEntries;
                }
            } elseif (!$payloadIsSubmitted) {
                $data['collectionPayload'] = '[]';
            }

            if (is_array($entries) && $entries !== []) {
                $data = self::copyEntryToRootFields($data, $entries[0]);
            }

            $event->setData($data);
        });
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $entry
     *
     * @return array<string, mixed>
     */
    private static function copyEntryToRootFields(array $data, array $entry): array
    {
        $data['locationId'] = $entry['locationId'];
        $data['remoteId'] = $entry['remoteId'];
        $data['type'] = $entry['type'];
        $data['altText'] = $entry['altText'];
        $data['caption'] = $entry['caption'];
        $data['watermarkText'] = $entry['watermarkText'];
        $data['tags'] = $entry['tags'];
        $data['cropSettings'] = $entry['cropSettings'];
        $data['source'] = $entry['source'];

        return $data;
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     *
     * @return array<int, array<string, mixed>>
     */
    private static function filterSubmittedLocationIds(array $entries, mixed $data): array
    {
        $allowedIds = array_flip(self::extractExistingLocationIds($data));

        return array_map(
            static function (array $entry) use ($allowedIds): array {
                $locationId = $entry['locationId'] ?? null;

                if ($locationId === null || $locationId === '' || !array_key_exists((string) $locationId, $allowedIds)) {
                    $entry['locationId'] = null;
                }

                return $entry;
            },
            $entries,
        );
    }

    /**
     * @return array<int, RemoteResourceLocation>
     */
    private static function extractRemoteMediaLocations(mixed $data): array
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

    /**
     * Persisted location ids present in the form's pre-set data. Used to reject
     * client-supplied locationIds that do not belong to this form.
     *
     * @return array<int, string>
     */
    private static function extractExistingLocationIds(mixed $data): array
    {
        return array_map(
            static fn (RemoteResourceLocation $location): string => (string) $location->getId(),
            array_filter(
                self::extractRemoteMediaLocations($data),
                static fn (RemoteResourceLocation $location): bool => $location->getId() !== null,
            ),
        );
    }
}

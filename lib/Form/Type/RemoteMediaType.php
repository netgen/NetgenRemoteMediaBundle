<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\Type;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaCollectionEntryExtractor;
use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaCollectionTransformer;
use Netgen\RemoteMedia\Service\RemoteResourceService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_intersect;
use function array_map;
use function array_replace;
use function count;
use function in_array;
use function is_array;
use function is_iterable;
use function is_string;
use function json_encode;

use const JSON_INVALID_UTF8_SUBSTITUTE;
use const JSON_THROW_ON_ERROR;

/**
 * Remote media form type.
 *
 * The model data is always an ordered Doctrine ArrayCollection of
 * RemoteResourceLocation objects (possibly empty), whatever the `upload_limit`
 * option is. The limit only decides how many resources the widget lets the
 * editor select, and how many a submission may contain.
 *
 * The whole selection is submitted as one JSON string, `collectionPayload`.
 */
final class RemoteMediaType extends AbstractType
{
    /**
     * @param DataTransformerInterface<mixed, mixed> $transformer Transformer of a single RemoteResourceLocation
     */
    public function __construct(
        private DataTransformerInterface $transformer,
        private ProviderInterface $provider,
        private RemoteResourceService $service,
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
            // The errors (eg. over the limit) belong to this field, not to the
            // whole form, so it's clear which field they are about.
            'error_bubbling' => false,
        ]);

        $resolver->setAllowedTypes('upload_limit', ['int', 'null']);
        $resolver->setAllowedValues('upload_limit', static fn (?int $value): bool => $value === null || $value >= 1);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locationSource = self::resolveLocationSource($options, $builder->getName());

        $builder->add('collectionPayload', HiddenType::class, ['required' => false]);

        $builder->addModelTransformer(
            new RemoteMediaCollectionTransformer($this->transformer, $options['upload_limit'], $locationSource),
        );

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) use ($locationSource): void {
            $data = $event->getData() ?? [];
            if (!is_array($data)) {
                return;
            }

            $form = $event->getForm();
            $payload = $data['collectionPayload'] ?? null;

            if ($payload === null || $payload === '') {
                // Nothing was submitted for the field (the widget did not load,
                // or the client does not know about it). That must not empty
                // the field, so the current value is submitted in its place.
                // Only an explicit "[]" clears the field.
                $payload = $form->getViewData()['collectionPayload'] ?? '[]';
            } else {
                try {
                    $payload = json_encode(
                        self::dropForeignLocationIds(
                            RemoteMediaCollectionEntryExtractor::fromPayload($payload, $locationSource),
                            $form->getData(),
                        ),
                        JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE,
                    );
                } catch (TransformationFailedException) {
                    // Left as submitted: the model transformer rejects it,
                    // which marks the form as invalid.
                }
            }

            $data['collectionPayload'] = $payload;

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

        $locationSource = self::resolveLocationSource($options, $form->getName());

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
            'location_source' => $locationSource,
            'remote_media_locations' => $this->resolveViewLocations($form, $locationSource),
        ]);
    }

    /**
     * @param array<string, mixed> $options
     */
    private static function resolveLocationSource(array $options, string $formName): string
    {
        return $options['location_source'] ?? 'form_' . $formName;
    }

    /**
     * A location can only be reused by the form it already belongs to. The ID
     * of any other location is dropped from the entry, which then gets a new
     * location instead of taking over the one of another field or entity.
     *
     * @param array<int, array<string, mixed>> $entries
     *
     * @return array<int, array<string, mixed>>
     */
    private static function dropForeignLocationIds(array $entries, mixed $currentData): array
    {
        $ownLocationIds = [];
        foreach (is_iterable($currentData) ? $currentData : [] as $location) {
            if ($location instanceof RemoteResourceLocation && $location->getId() !== null) {
                $ownLocationIds[] = $location->getId();
            }
        }

        return array_map(
            static function (array $entry) use ($ownLocationIds): array {
                if (!in_array($entry['locationId'], $ownLocationIds, true)) {
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
    private function resolveViewLocations(FormInterface $form, string $locationSource): array
    {
        if ($form->isSubmitted() && !$form->isSynchronized()) {
            // The submission was rejected, so it never became model data. It is
            // shown back as submitted, for the editor to keep the new order,
            // the added resources and the crops while fixing the error.
            return $this->buildSubmittedLocations(
                $form->get('collectionPayload')->getData(),
                $locationSource,
            );
        }

        $data = $form->getData();

        return is_iterable($data) ? RemoteMediaCollectionTransformer::sortByPosition($data) : [];
    }

    /**
     * Builds locations out of a submitted payload for display only. They are
     * detached copies, so nothing of a rejected submission can get stored.
     *
     * @return array<int, RemoteResourceLocation>
     */
    private function buildSubmittedLocations(mixed $payload, string $locationSource): array
    {
        $locations = [];
        foreach (RemoteMediaCollectionEntryExtractor::fromPayloadLenient($payload, $locationSource) as $entry) {
            $resource = $this->loadResource($entry['remoteId']);
            if (!$resource instanceof RemoteResource) {
                continue;
            }

            $resource = clone $resource;
            $resource
                ->setAltText($entry['altText'])
                ->setCaption($entry['caption'])
                ->setTags($entry['tags']);

            $locations[] = new RemoteResourceLocation(
                remoteResource: $resource,
                source: $entry['source'],
                cropSettings: $this->service->resolveCropSettings($entry['cropSettings']),
                watermarkText: $entry['watermarkText'],
                id: $entry['locationId'],
                altTextOverride: $entry['altTextOverride'],
                captionOverride: $entry['captionOverride'],
                position: count($locations),
            );
        }

        return $locations;
    }

    private function loadResource(string $remoteId): ?RemoteResource
    {
        try {
            return $this->provider->loadByRemoteId($remoteId);
        } catch (RemoteResourceNotFoundException) {
        }

        try {
            return $this->provider->loadFromRemote($remoteId);
        } catch (RemoteResourceNotFoundException) {
            return null;
        }
    }
}

<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\Type;

use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaCollectionEntryExtractor;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_flip;
use function array_key_exists;
use function array_map;
use function array_replace;
use function is_array;
use function json_encode;

use const JSON_INVALID_UTF8_SUBSTITUTE;

/**
 * Ordered multi-resource remote media form type.
 *
 * Always submits/returns a Doctrine ArrayCollection of RemoteResourceLocation
 * objects (possibly empty), never a bare location. Cardinality is controlled
 * by the `upload_limit` option: null means unlimited, a positive integer caps
 * the collection size (enforced server-side during reverse transformation).
 */
final class RemoteMediaCollectionType extends AbstractRemoteMediaType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('upload_limit', null);
        $resolver->setAllowedTypes('upload_limit', ['int', 'null']);
        $resolver->setAllowedValues('upload_limit', static fn ($value) => $value === null || $value >= 0);
        $resolver->setNormalizer('upload_limit', static function (Options $options, $value) {
            // Legacy convention: 0 means unlimited.
            return $value === 0 ? null : $value;
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

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
                $firstEntry = $entries[0];
                $data['locationId'] = $firstEntry['locationId'];
                $data['remoteId'] = $firstEntry['remoteId'];
                $data['type'] = $firstEntry['type'];
                $data['altText'] = $firstEntry['altText'];
                $data['caption'] = $firstEntry['caption'];
                $data['watermarkText'] = $firstEntry['watermarkText'];
                $data['tags'] = $firstEntry['tags'];
                $data['cropSettings'] = $firstEntry['cropSettings'];
                $data['source'] = $firstEntry['source'];
            }

            $event->setData($data);
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars = array_replace($view->vars, [
            'upload_limit' => $options['upload_limit'],
            'is_collection' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        // Reuse the remote_media form theme block; the widget switches on
        // the is_collection / upload_limit view vars.
        return 'remote_media';
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
}

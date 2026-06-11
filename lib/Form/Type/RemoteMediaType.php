<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\Type;

use Netgen\RemoteMedia\Form\DataTransformer\RemoteMediaCollectionEntryExtractor;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use function array_replace;
use function is_array;

/**
 * Single-resource remote media form type.
 *
 * Always submits/returns one RemoteResourceLocation (or null). For ordered
 * multi-resource values use RemoteMediaCollectionType instead.
 */
final class RemoteMediaType extends AbstractRemoteMediaType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

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
            'upload_limit' => 1,
            'is_collection' => false,
        ]);
    }
}

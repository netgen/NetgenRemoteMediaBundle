<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\Type;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\Folder;
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
use function is_string;

final class RemoteMediaType extends AbstractType
{
    private DataTransformerInterface $transformer;

    private ProviderInterface $provider;

    public function __construct(DataTransformerInterface $transformer, ProviderInterface $provider)
    {
        $this->transformer = $transformer;
        $this->provider = $provider;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'allowed_visibilities' => [],
            'allowed_types' => [],
            'allowed_tags' => [],
            'parent_folder' => null,
            'folder' => null,
            'upload_context' => [],
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
            ->add('cropSettings', HiddenType::class);

        $builder->addModelTransformer($this->transformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $parentFolder = null;
        if ($options['parent_folder']) {
            $parentFolder = ($options['parent_folder'] instanceof Folder)
                ? $options['parent_folder']
                : Folder::fromPath($options['parent_folder']);
        }

        $folder = null;
        if ($options['folder']) {
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
        ]);
    }
}

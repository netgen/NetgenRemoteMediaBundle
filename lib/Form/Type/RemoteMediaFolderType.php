<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\Type;

use Netgen\RemoteMedia\API\Values\Folder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;

final class RemoteMediaFolderType extends AbstractType
{
    public function __construct(
        private DataTransformerInterface $transformer,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'parent_folder' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('folder', HiddenType::class, ['required' => false]);

        $builder->addModelTransformer($this->transformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $parentFolder = null;
        if ($options['parent_folder'] !== null) {
            $parentFolder = ($options['parent_folder'] instanceof Folder)
                ? $options['parent_folder']
                : Folder::fromPath($options['parent_folder']);
        }

        $view->vars = array_replace($view->vars, [
            'parent_folder' => $parentFolder instanceof Folder
                ? ['id' => $parentFolder->getPath(), 'label' => $parentFolder->getName()]
                : null,
        ]);
    }
}

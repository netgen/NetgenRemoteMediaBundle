<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RemoteMediaType extends AbstractType
{
    private DataTransformerInterface $transformer;

    public function __construct(DataTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
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
            ->add('tags', HiddenType::class, ['required' => false])
            ->add('cropSettings', HiddenType::class);

        $builder->addModelTransformer($this->transformer);
    }
}

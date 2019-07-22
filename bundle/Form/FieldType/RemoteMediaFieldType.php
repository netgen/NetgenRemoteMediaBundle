<?php

namespace Netgen\Bundle\RemoteMediaBundle\Form\FieldType;

use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\API\Repository\Values\Content\Field;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RemoteMediaFieldType extends AbstractType
{
    /**
     * @var \eZ\Publish\API\Repository\FieldTypeService
     */
    private $fieldTypeService;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    private $remoteMediaProvider;

    public function __construct(FieldTypeService $fieldTypeService, RemoteMediaProvider $remoteMediaProvider)
    {
        $this->fieldTypeService = $fieldTypeService;
        $this->remoteMediaProvider = $remoteMediaProvider;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array('field'));
        $resolver->setAllowedTypes('field', Field::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('resource_id', HiddenType::class)
            ->add('resource_url', HiddenType::class)
            ->add('size', HiddenType::class)
            ->add('alt_text', TextType::class)
            ->add('media_type', HiddenType::class)
            ->add('tags', TextType::class)
            ->add('url', HiddenType::class)
            ->add('height', HiddenType::class)
            ->add('width', HiddenType::class)
            ->addModelTransformer(
                new FieldValueTransformer(
                    $this->fieldTypeService->getFieldType('ngremotemedia'),
                    $options['field'],
                    $this->remoteMediaProvider
                )
            );
    }

    public function getBlockPrefix()
    {
        return 'ezplatform_fieldtype_ngremotemedia';
    }
}

<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Form\FieldType;

use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\API\Repository\Values\Content\Field;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\UpdateFieldHelper;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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

    /**
     * @var UpdateFieldHelper
     */
    private $updateHelper;

    public function __construct(FieldTypeService $fieldTypeService, RemoteMediaProvider $remoteMediaProvider, UpdateFieldHelper $updateHelper)
    {
        $this->fieldTypeService = $fieldTypeService;
        $this->remoteMediaProvider = $remoteMediaProvider;
        $this->updateHelper = $updateHelper;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['field']);
        $resolver->setAllowedTypes('field', Field::class);
        $resolver->setDefault('translation_domain', 'ngremotemedia');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('resource_id', HiddenType::class)
            ->add('alt_text', TextType::class, ['label' => 'ngrm.edit.form.field.alt_text'])
            ->add(
                'tags',
                ChoiceType::class,
                [
                    'multiple' => true,
                    'choices' => ['{{tag}}' => true],
                    'choice_attr' => static fn () => ['v-for' => 'tag in allTags', ':value' => 'tag'],
                    'label' => 'ngrm.edit.form.field.tags',
                ],
            )
            ->add('image_variations', HiddenType::class)
            ->add('media_type', HiddenType::class)
            ->add('new_file', FileType::class)
            ->addModelTransformer(
                new FieldValueTransformer(
                    $this->fieldTypeService->getFieldType('ngremotemedia'),
                    $options['field'],
                    $this->remoteMediaProvider,
                    $this->updateHelper,
                ),
            );

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $form->remove('tags');

            $form->add('tags', ChoiceType::class, [
                'multiple' => true,
                'choices' => $data['tags'] ?? [],
                'choice_attr' => static fn () => ['v-for' => 'tag in allTags', ':value' => 'tag'],
                'label' => 'ngrm.edit.form.field.tags',
            ]);
        });
    }

    public function getBlockPrefix()
    {
        return 'ezplatform_fieldtype_ngremotemedia';
    }
}

<?php

namespace Netgen\Bundle\RemoteMediaBundle\Command;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\InputValue;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Example usage:
 * php ezpublish/console netgen:remotemedia:add:field ng_blog_post remote_image "Remote image" --formats=T1,200x200 --formats=T2,250x250
 */
class AddRemoteMediaFieldCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('netgen:remotemedia:add:field')
            ->setDescription('This command will add ng_remote_media field type to the provided content type')
            ->addArgument(
                'content_type_identifier',
                InputArgument::REQUIRED,
                'Identifier of the content type to edit'
            )
            ->addArgument(
                'field_identifier',
                InputArgument::REQUIRED,
                'Field identifier on the content type'
            )
            ->addArgument(
                'field_name',
                InputArgument::REQUIRED,
                'Field name'
            )
            ->addOption(
                'field_position',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Position of the field on the content type',
                0
            )
            ->addOption(
                'formats',
                'f',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                "Variations in the format [variationName,WxH]",
                array()
            )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contentTypeIdentifier = $input->getArgument('content_type_identifier');
        $fieldDefIdentifier = $input->getArgument('field_identifier');
        $fieldName = $input->getArgument('field_name');
        $fieldPosition = (int)$input->getOption('field_position');

        $formats = $input->getOption('formats');

        $variations = array();
        foreach ($formats as $format) {
            $exploded = explode(',', $format);
            $variations[$exploded[0]] = $exploded[1];
        }

        $repository = $this->getContainer()->get('ezpublish.api.repository');

        $repository->setCurrentUser(
            $repository->getUserService()->loadUser(14)
        );

        $repository->beginTransaction();

        $contentTypeService = $repository->getContentTypeService();
        $contentType = $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
        $contentTypeNames = $contentType->getNames();
        $languageCodes = array_keys($contentTypeNames);
        $names = array();
        foreach($languageCodes as $languageCode) {
            $names[$languageCode] = $fieldName;
        }

        $contentTypeDraft = $contentTypeService->createContentTypeDraft($contentType);

        try {
            $fieldDefCreate = $contentTypeService->newFieldDefinitionCreateStruct($fieldDefIdentifier, 'ngremotemedia');
            $fieldDefCreate->position = $fieldPosition;
            $fieldDefCreate->names = $names;
            if (!empty($variations)) {
                $fieldDefCreate->fieldSettings = array(
                    'formats' => $variations
                );
            }

            $contentTypeService->addFieldDefinition($contentTypeDraft, $fieldDefCreate);
            $contentTypeService->publishContentTypeDraft($contentTypeDraft);

            $repository->commit();
        } catch (\Exception $e) {
            $repository->rollback();
            $output->writeln('<error>' . $e->getMessage() . '<error>');
            dump($e->getTraceAsString());

            return;
        }

        $contentType = $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
        dump($contentType);

        return;
    }
}

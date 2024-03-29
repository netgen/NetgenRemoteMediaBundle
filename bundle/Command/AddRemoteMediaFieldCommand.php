<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Command;

use Exception;
use eZ\Publish\API\Repository\Repository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function array_keys;
use function dump;
use function sprintf;

/**
 * Example usage:
 * php ezpublish/console netgen:ngremotemedia:add:field ng_blog_post remote_image "Remote image" --formats=T1,200x200 --formats=T2,250x250.
 */
class AddRemoteMediaFieldCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('netgen:ngremotemedia:add:field')
            ->setDescription('This command will add ng_remote_media field type to the provided content type')
            ->addArgument(
                'content_type_identifier',
                InputArgument::REQUIRED,
                'Identifier of the content type to edit',
            )
            ->addArgument(
                'field_identifier',
                InputArgument::REQUIRED,
                'Field identifier on the content type',
            )
            ->addArgument(
                'field_name',
                InputArgument::REQUIRED,
                'Field name',
            )
            ->addOption(
                'field_position',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Position of the field on the content type',
                0,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contentTypeIdentifier = $input->getArgument('content_type_identifier');
        $fieldDefIdentifier = $input->getArgument('field_identifier');
        $fieldName = $input->getArgument('field_name');
        $fieldPosition = (int) $input->getOption('field_position');

        $repository = $this->getContainer()->get('ezpublish.api.repository');

        $contentTypeService = $repository->getContentTypeService();
        $contentType = $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
        $contentTypeNames = $contentType->getNames();
        $languageCodes = array_keys($contentTypeNames);
        $names = [];
        foreach ($languageCodes as $languageCode) {
            $names[$languageCode] = $fieldName;
        }

        $repository->beginTransaction();

        $contentTypeDraft = $repository->sudo(
            static function (Repository $repository) use ($contentType) {
                return $repository->getContentTypeService()->createContentTypeDraft($contentType);
            },
        );

        $fieldDefCreate = $contentTypeService->newFieldDefinitionCreateStruct($fieldDefIdentifier, 'ngremotemedia');
        $fieldDefCreate->position = $fieldPosition;
        $fieldDefCreate->names = $names;

        try {
            $repository->sudo(
                static function (Repository $repository) use ($contentTypeDraft, $fieldDefCreate) {
                    $repository->getContentTypeService()->addFieldDefinition($contentTypeDraft, $fieldDefCreate);

                    return $repository->getContentTypeService()->publishContentTypeDraft($contentTypeDraft);
                },
            );

            $repository->commit();
        } catch (Exception $exception) {
            $repository->rollback();
            $output->writeln('<error>' . $exception->getMessage() . '<error>');
            dump($exception->getTraceAsString());

            return;
        }

        $contentType = $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
        $output->writeln(sprintf('<info>Added new field %s to content type %s</info>', $fieldDefIdentifier, $contentTypeIdentifier));
    }
}

<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Command;

use Countable;
use InvalidArgumentException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Exception;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\UploadFile;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class MigrateEzImageCommand extends ContainerAwareCommand
{
    protected $rootLocationId;

    protected $webPath;

    /** @var \Symfony\Component\Console\Input\InputInterface */
    protected $input;

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    protected $output;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    protected $contentTypeService;

    /** @var \eZ\Publish\API\Repository\ContentService */
    protected $contentService;

    /** @var \eZ\Publish\API\Repository\LocationService */
    protected $locationService;

    /** @var \eZ\Publish\API\Repository\SearchService */
    protected $searchService;

    /** @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider */
    protected $remoteMediaProvider;

    /** @var \eZ\Publish\Core\Helper\FieldHelper */
    protected $fieldHelper;

    /** @var \eZ\Publish\Core\Helper\TranslationHelper */
    protected $translationHelper;

    /** @var \Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper */
    protected $questionHelper;

    protected function configure()
    {
        $this
            ->setName('netgen:ngremotemedia:migrate:ezimage')
            ->setDescription('This command will migrate ezimage field to ngremotemedia field.')
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE
            )
            ->addOption(
                'continue-on-error',
                'c',
                InputOption::VALUE_NONE
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->contentTypeService = $this->getContainer()->get('ezpublish.api.service.content_type');
        $this->searchService = $this->getContainer()->get('ezpublish.api.service.search');
        $this->contentService = $this->getContainer()->get('ezpublish.api.service.content');
        $this->locationService = $this->getContainer()->get('ezpublish.api.service.location');
        $this->remoteMediaProvider = $this->getContainer()->get('netgen_remote_media.provider');
        $this->fieldHelper = $this->getContainer()->get('ezpublish.field_helper');
        $this->translationHelper = $this->getContainer()->get('ezpublish.translation_helper');
        $this->questionHelper = $this->getHelper('question');
        $this->rootLocationId = $this->getContainer()->get('ezpublish.config.resolver')->getParameter('content.tree_root.location_id');
        $this->webPath = $this->getContainer()->getParameter('kernel.root_dir') . '/../web';
    }

    protected function listFields()
    {
        $this->output->writeln('');
        $this->output->writeln('List of all ez image and remotemedia fields');
        $this->output->writeln('');

        $contentTypes = [];
        $contentTypeGroups = $this->contentTypeService->loadContentTypeGroups();
        foreach ($contentTypeGroups as $contentTypeGroup) {
            $contentTypesResult = $this->contentTypeService->loadContentTypes($contentTypeGroup);
            foreach ($contentTypesResult as $contentType) {
                $fields = $contentType->getFieldDefinitions();
                foreach ($fields as $field) {
                    if ($field->fieldTypeIdentifier === 'ezimage' || $field->fieldTypeIdentifier === 'ngremotemedia') {
                        $contentTypes[$field->id] = [
                            $contentType->getName($contentType->mainLanguageCode),
                            $contentType->identifier,
                            $field->fieldTypeIdentifier,
                            $field->getName($contentType->mainLanguageCode),
                            $field->identifier,
                        ];
                    }
                }
            }
        }

        $table = new Table($this->output);
        $table
            ->setHeaders(['Content type', 'Content type identifier', 'Field type', 'Field name', 'Field identifier'])
            ->setRows($contentTypes);
        $table->render();
    }

    protected function getContentType()
    {
        $question = new Question('Please select content type identifier to migrate: ', '');
        $contentTypeIdentifier = $this->questionHelper->ask($this->input, $this->output, $question);

        return $this->contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
    }

    protected function getFieldIdentifier(ContentType $contentType, $type)
    {
        $questionMessage = [
            'source' => 'Please select identifier of source field (ezimage): ',
            'destination' => 'Please select identifier of the destination field (ngremotemedia): ',
        ];

        $question = new Question($questionMessage[$type], '');
        $fieldIdentifier = $this->questionHelper->ask($this->input, $this->output, $question);

        $field = $contentType->getFieldDefinition($fieldIdentifier);

        if (!$field) {
            throw new InvalidArgumentException('Could not find the field with the selected identifier.');
        }

        return $fieldIdentifier;
    }

    protected function getOverwrite()
    {
        $question = new Question('Overwrite existing data on ngremotemedia field if exists? (y/n): ', 'n');
        $overwrite = $this->questionHelper->ask($this->input, $this->output, $question);

        return $overwrite === 'y';
    }

    protected function getContinue()
    {
        $question = new Question('Continue? (y/n): ', 'y');
        $continue = $this->questionHelper->ask($this->input, $this->output, $question);

        return $continue === 'y';
    }

    protected function searchLocations(ContentType $contentType, $offset = 0, $limit = 100)
    {
        $rootLocation = $this->locationService->loadLocation($this->rootLocationId);

        $query = new LocationQuery();
        $query->filter = new LogicalAnd(
            [
                new Subtree($rootLocation->pathString),
                new ContentTypeIdentifier($contentType->identifier),
            ]
        );
        $query->limit = $limit;
        $query->offset = $offset;

        return $this->searchService->findLocations($query, [], false);
    }

    protected function migrateField(Location $location, $ezimageFieldIdentifier, $remoteMediaFieldIdentifier, $forceUpdate = false, $dryRun = false)
    {
        $content = $this->contentService->loadContentByContentInfo(
            $location->getContentInfo()
        );

        if ($this->fieldHelper->isFieldEmpty($content, $ezimageFieldIdentifier)) {
            $this->output->writeln("<error>Field '{$ezimageFieldIdentifier}' for the content {$content->id} is empty. Skipping...</error>");

            return false;
        }

        if (!$forceUpdate) {
            if (!$this->fieldHelper->isFieldEmpty($content, $remoteMediaFieldIdentifier)) {
                $this->output->writeln("<error>RemoteMedia field is not empty, and 'overwrite' was not selected. Skipping...</error>");

                return false;
            }
        }

        // @todo: handle translations
        $ezImageValue = $this->translationHelper->getTranslatedField($content, $ezimageFieldIdentifier)->value;

        $uploadFile = UploadFile::fromEzImageValue($ezImageValue, $this->webPath);

        if ($dryRun) {
            $this->output->writeln('<info>Would migrate image: ' . $uploadFile->uri() . '</info>');

            return true;
        }

        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $repository->beginTransaction();

        try {
            $contentDraft = $repository->sudo(
                static function (Repository $repository) use ($content) {
                    return $repository->getContentService()->createContentDraft($content->contentInfo);
                }
            );
            $repository->commit();
        } catch (Exception $e) {
            $repository->rollback();

            throw $e;
        }

        $value = $this->remoteMediaProvider->upload($uploadFile);

        $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField($remoteMediaFieldIdentifier, $value);

        $repository->beginTransaction();
        try {
            $repository->sudo(
                static function (Repository $repository) use ($contentDraft, $contentUpdateStruct) {
                    $contentDraft = $repository->getContentService()->updateContent($contentDraft->versionInfo, $contentUpdateStruct);

                    return $repository->getContentService()->publishVersion($contentDraft->versionInfo);
                }
            );
            $repository->commit();
        } catch (Exception $e) {
            $repository->rollback();

            throw $e;
        }

        return true;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dryRun = $input->getOption('dry-run');
        $continueOnError = $input->getOption('continue-on-error');

        $this->listFields();
        $output->writeln('');

        $contentType = $this->getContentType();
        $ezimageFieldIdentifier = $this->getFieldIdentifier($contentType, 'source');
        $remoteMediaFieldIdentifier = $this->getFieldIdentifier($contentType, 'destination');
        $overwrite = $this->getOverwrite();

        $siteaccess = $this->getContainer()->get('ezpublish.siteaccess');
        $updated = 0;

        $offset = 0;
        $limit = 5;
        do {
            $result = $this->searchLocations($contentType, $offset, $limit);

            if ($offset === 0) {
                $this->output->writeln("<info>Found {$result->totalCount} objects for siteaccess '{$siteaccess->name}'...</info>");

                $continue = $this->getContinue();

                if (!$continue) {
                    return;
                }
            }

            foreach ($result->searchHits as $searchHit) {
                try {
                    /** @var Location $location */
                    $location = $searchHit->valueObject;
                    $this->output->writeln('Migrating: ' . $location->getContentInfo()->id . ' - "' . $location->getContentInfo()->name . '"');

                    $migrateResult = $this->migrateField(
                        $location,
                        $ezimageFieldIdentifier,
                        $remoteMediaFieldIdentifier,
                        $overwrite,
                        $dryRun
                    );

                    if ($migrateResult) {
                        $this->output->writeln('<info>Success</info>');
                        $this->output->writeln('-----------------------------');
                        ++$updated;
                    } else {
                        $this->output->writeln('<error>No change</error>');
                        $this->output->writeln('-----------------------------');
                    }
                } catch (Exception $e) {
                    if ($continueOnError) {
                        $this->output->writeln("<error>ContentId: {$searchHit->valueObject->contentInfo->id}: " . $e->getMessage() . '</error>');
                    } else {
                        throw $e;
                    }
                }
            }

            $offset += $limit;
        } while ((is_array($result->searchHits) || $result->searchHits instanceof Countable ? \count($result->searchHits) : 0) > 0);

        $this->output->writeln("<info>Updated {$updated} objects.</info>");
    }
}

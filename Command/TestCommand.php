<?php

namespace Netgen\Bundle\RemoteMediaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('remotemedia:test')
            ->setDescription('Testing.')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->getContainer()->get('ezpublish.api.repository');

        $repository->setCurrentUser(
            $repository->getUserService()->loadUser(14)
        );

        $repository->beginTransaction();

        //$contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        //$contentType = $contentTypeService->loadContentTypeByIdentifier('ng_article');

        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');
        $imagePath = $rootDir . '/test.jpg';

        try {
            /*$contentTypeDraft = $contentTypeService->createContentTypeDraft($contentType);
            $fieldDefCreate = $contentTypeService->newFieldDefinitionCreateStruct('test_image', 'ngremotemedia');

            $contentTypeService->addFieldDefinition($contentTypeDraft, $fieldDefCreate);
            $contentTypeService->publishContentTypeDraft($contentTypeDraft);*/


            $contentInfo = $contentService->loadContentInfo( 538 );
            $contentDraft = $contentService->createContentDraft( $contentInfo );
            $contentUpdateStruct = $contentService->newContentUpdateStruct();
            $contentUpdateStruct->initialLanguageCode = 'nor-NO'; // set language for new version
            $contentUpdateStruct->setField( 'test_image', $imagePath );
            // update and publish draft
            $contentDraft = $contentService->updateContent( $contentDraft->versionInfo, $contentUpdateStruct );
            $content = $contentService->publishVersion( $contentDraft->versionInfo );


            $repository->commit();
        } catch (\Exception $e) {
            $repository->rollback();

            die(dump($e->getMessage()));
        }

        //$contentType = $contentTypeService->loadContentTypeByIdentifier('ng_article');
        die(dump($content->getFieldValue('test_image')));
    }
}

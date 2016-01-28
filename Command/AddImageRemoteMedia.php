<?php

namespace Netgen\Bundle\RemoteMediaBundle\Command;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\InputValue;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddImageRemoteMedia extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('netgen:remotemedia:add:data')
            ->setDescription('This command will add item to the remotemedia field on the provided content')
            ->addArgument(
                'content_id',
                InputArgument::REQUIRED,
                'Id of the content to edit'
            )
            ->addArgument(
                'field_identifier',
                InputArgument::REQUIRED,
                'Field identifier to edit'
            )
            ->addArgument(
                'image_path',
                InputArgument::REQUIRED,
                'Image path, relative to ezpublish folder'
            )
            ->addOption(
                'alt_text',
                'a',
                InputOption::VALUE_OPTIONAL,
                'Alternative text for the image',
                ''
            )
            ->addOption(
                'caption',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Caption for the image',
                ''
            )
            ->addOption(
                'language',
                'l',
                InputOption::VALUE_OPTIONAL,
                ''
            )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contentId = $input->getArgument('content_id');
        $fieldIdentifier = $input->getArgument('field_identifier');
        $imagePath = $input->getArgument('image_path');

        $altText = $input->getOption('alt_text');
        $caption = $input->getOption('caption');
        $language = $input->getOption('language');

        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $contentService = $repository->getContentService();
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');
        $imagePath = $rootDir . $imagePath;

        $repository->beginTransaction();

        $contentInfo = $contentService->loadContentInfo($contentId);
        $contentDraft = $contentService->createContentDraft($contentInfo);
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = !empty($language) ? $language : $contentInfo->mainLanguageCode;

        $imageInput = new InputValue();
        $imageInput->input_uri = $imagePath;
        $imageInput->alt_text = $altText;
        $imageInput->caption = $caption;
        // it is also possible to set coordinations per named variation
        //$imageInput->variations = array(
        //    'T1' => array('x' => 20, 'y' => 50)
        //);
        $contentUpdateStruct->setField( $fieldIdentifier, $imageInput );
        try {
            $contentDraft = $contentService->updateContent( $contentDraft->versionInfo, $contentUpdateStruct );
            $content = $contentService->publishVersion( $contentDraft->versionInfo );

            $repository->commit();
        } catch (\Exception $e) {
            $repository->rollback();
            $output->writeln('<error>' . $e->getMessage() . '<error>');
            dump($e->getTraceAsString());

            return;
        }

        dump($content);

        return;
    }
}

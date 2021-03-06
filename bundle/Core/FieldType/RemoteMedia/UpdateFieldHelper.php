<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use function array_pop;
use function count;
use function explode;
use function implode;
use function pathinfo;
use const PATHINFO_FILENAME;

final class UpdateFieldHelper
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    private $remoteMediaProvider;

    /**
     * UpdateFieldHelper constructor.
     */
    public function __construct(RemoteMediaProvider $remoteMediaProvider)
    {
        $this->remoteMediaProvider = $remoteMediaProvider;
    }

    public function updateValue(Value $oldValue, AdminInputValue $adminInputValue)
    {
        if ($adminInputValue->getResourceId() === '' && empty($adminInputValue->getNewFile())) {
            return new Value();
        }

        if (empty($adminInputValue->getNewFile())) {
            $updatedValue = $this->remoteMediaProvider->getRemoteResource($adminInputValue->getResourceId(), $adminInputValue->getMediaType());
        } else {
            $options = [];
            if ($adminInputValue->getResourceId()) {
                $exploded = explode('/', $adminInputValue->getResourceId());
                $options['filename'] = pathinfo(array_pop($exploded), PATHINFO_FILENAME);
                if (count($exploded) > 0) {
                    $options['folder'] = implode('/', $exploded);
                }
            }

            $updatedValue = $this->remoteMediaProvider->upload($adminInputValue->getNewFile(), $options);

            $oldValue = new Value(); // reset the context and variations
        }

        $this->updateContext($updatedValue, $oldValue, $adminInputValue);

        $updatedValue->variations = $adminInputValue->getVariations();

        return $updatedValue;
    }

    private function updateContext(Value $updatedValue, Value $oldValue, AdminInputValue $input)
    {
        if ($oldValue->metaData['alt_text'] === $input->getAltText() && $oldValue->metaData['tags'] === $input->getTags()) {
            $updatedValue->metaData['alt_text'] = $input->getAltText();
            $updatedValue->metaData['tags'] = $input->getTags();

            return;
        }

        $dataToChange = [];
        if ($oldValue->metaData['alt_text'] !== $input->getAltText()) {
            $dataToChange['alt_text'] = $input->getAltText();
            $updatedValue->metaData['alt_text'] = $input->getAltText();
        }

        if ($oldValue->metaData['tags'] !== $input->getTags()) {
            if (count($input->getTags()) === 0) {
                $this->remoteMediaProvider->removeAllTagsFromResource($updatedValue->resourceId, $updatedValue->resourceType);
            }

            if (count($input->getTags()) > 0) {
                $this->remoteMediaProvider->updateTags(
                    $updatedValue->resourceId,
                    count($input->getTags()) > 0 ? implode(',', $input->getTags()) : null,
                    $updatedValue->resourceType,
                );
            }

            $updatedValue->metaData['tags'] = $input->getTags();
        }

        if (!empty($dataToChange)) {
            $this->remoteMediaProvider->updateResourceContext(
                $updatedValue->resourceId,
                $updatedValue->resourceType,
                $dataToChange,
            );
        }
    }
}

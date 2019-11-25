<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;

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
            // @todo find which folder to upload to
            $updatedValue = $this->remoteMediaProvider->upload($adminInputValue->getNewFile());
            $oldValue = new Value(); // reset the context and variations
        }

        $this->updateContext($updatedValue, $oldValue, $adminInputValue);
        $this->updateVariations($updatedValue, $oldValue, $adminInputValue);

        return $updatedValue;
    }

    private function updateContext(Value $updatedValue, Value $oldValue, AdminInputValue $input)
    {
        if ($oldValue->metaData['alt_text'] === $input->getAltText() && $oldValue->metaData['tags'] === $input->getTags()) {
            return;
        }

        $dataToChange = [];
        if ($oldValue->metaData['alt_text'] !== $input->getAltText()) {
            $dataToChange['alt_text'] = $input->getAltText();
            $updatedValue->metaData['alt_text'] = $input->getAltText();
        }

        if ($oldValue->metaData['tags'] !== $input->getTags()) {
            $dataToChange['tags'] = $input->getTags();
            $updatedValue->metaData['tags'] = $input->getAltText();
        }

        if (!empty($dataToChange)) {
            $this->remoteMediaProvider->updateResourceContext(
                $updatedValue->resourceId,
                $updatedValue->mediaType,
                $dataToChange
            );
        }
    }

    private function updateVariations(Value $updatedValue, Value $oldValue, AdminInputValue $input)
    {
        $updatedValue->variations = $input->getVariations() + $oldValue->variations;
    }
}

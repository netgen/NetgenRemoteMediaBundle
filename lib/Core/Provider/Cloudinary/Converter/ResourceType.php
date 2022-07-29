<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Converter;

use Netgen\RemoteMedia\API\Values\RemoteResource;
use function in_array;

final class ResourceType
{
    /**
     * Cloudinary supported audio formats.
     *
     * @var string[]
     */
    private array $audioFormats = ['aac', 'aiff', 'amr', 'flac', 'm4a', 'mp3', 'ogg', 'opus', 'wav'];

    /**
     * Document formats that Cloudinary treats as image.
     *
     * @var string[]
     */
    private array $documentFormats = ['pdf', 'doc', 'docx'];

    public function fromCloudinaryData(string $type, ?string $format = null): string
    {
        switch ($type) {
            case 'video':
                if ($format !== null && $this->isAudioFormat($format)) {
                    return RemoteResource::TYPE_AUDIO;
                }

                return RemoteResource::TYPE_VIDEO;

            case 'image':
                if ($format !== null && $this->isDocumentFormat($format)) {
                    return RemoteResource::TYPE_DOCUMENT;
                }

                return RemoteResource::TYPE_IMAGE;
            default:
                return RemoteResource::TYPE_OTHER;
        }
    }

    public function toCloudinaryType(string $type): string
    {
        switch ($type) {
            case RemoteResource::TYPE_IMAGE:
            case RemoteResource::TYPE_DOCUMENT:
                return 'image';

            case RemoteResource::TYPE_VIDEO:
            case RemoteResource::TYPE_AUDIO:
                return 'video';

            default:
                return 'raw';
        }
    }

    private function isAudioFormat(string $format): bool
    {
        return in_array($format, $this->audioFormats, true);
    }

    private function isDocumentFormat(string $format): bool
    {
        return in_array($format, $this->documentFormats, true);
    }
}

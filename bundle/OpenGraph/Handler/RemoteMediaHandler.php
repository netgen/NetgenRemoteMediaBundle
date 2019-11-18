<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\OpenGraph\Handler;

use Exception;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Helper\FieldHelper;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\Bundle\OpenGraphBundle\Exception\FieldEmptyException;
use Netgen\Bundle\OpenGraphBundle\Handler\FieldType\Handler;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RequestStack;

class RemoteMediaHandler extends Handler
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    protected $provider;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\HttpFoundation\RequestStack
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        FieldHelper $fieldHelper,
        TranslationHelper $translationHelper,
        RemoteMediaProvider $provider,
        ContentTypeService $contentTypeService,
        RequestStack $requestStack,
        LoggerInterface $logger = null
    ) {
        parent::__construct($fieldHelper, $translationHelper);

        $this->provider = $provider;
        $this->contentTypeService = $contentTypeService;
        $this->requestStack = $requestStack;
        $this->logger = $logger instanceof LoggerInterface ? $logger : new NullLogger();
    }

    /**
     * Returns if this field type handler supports current field.
     *
     * @return bool
     */
    protected function supports(Field $field)
    {
        return $field->value instanceof Value;
    }

    /**
     * Returns the field value, converted to string.
     *
     * @param string $tagName
     *
     * @throws FieldEmptyException
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function getFieldValue(Field $field, $tagName, array $params = [])
    {
        if ($this->fieldHelper->isFieldEmpty($this->content, $params[0])) {
            throw new FieldEmptyException($field->fieldDefIdentifier);
        }

        try {
            $media = $this->translationHelper->getTranslatedField($this->content, $params[0])->value;

            if (!isset($params[1])) {
                return $media->secure_url;
            }

            $contentType = $this->contentTypeService->loadContentType(
                $this->content->versionInfo->contentInfo->contentTypeId
            );

            $variation = $this->provider->buildVariation($media, $contentType->identifier, $params[1]);

            return $variation->url;
        } catch (Exception $exception) {
            $this->logger->error(
                sprintf('Open Graph remote media handler: Error while getting media with id %s: ', $field->value->resourceId) . $exception->getMessage()
            );
        }

        throw new FieldEmptyException($field->fieldDefIdentifier);
    }

    /**
     * Returns fallback value.
     *
     * @param string $tagName
     *
     * @return string
     */
    protected function getFallbackValue($tagName, array $params = [])
    {
        if (!empty($params[2]) && ($request = $this->requestStack->getCurrentRequest()) !== null) {
            return $request->getUriForPath('/' . \ltrim($params[2], '/'));
        }

        return '';
    }
}

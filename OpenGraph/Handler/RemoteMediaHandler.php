<?php

namespace Netgen\Bundle\RemoteMediaBundle\OpenGraph\Handler;

use eZ\Publish\API\Repository\ContentTypeService;
use Netgen\Bundle\OpenGraphBundle\Exception\FieldEmptyException;
use Netgen\Bundle\OpenGraphBundle\Handler\FieldType\Handler;
use eZ\Publish\API\Repository\Values\Content\Field;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use eZ\Publish\Core\Helper\FieldHelper;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Psr\Log\LoggerInterface;
use Exception;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RequestStack;

class RemoteMediaHandler extends Handler
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     *
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
     * Constructor
     *
     * @param \eZ\Publish\Core\Helper\FieldHelper $fieldHelper
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider $provider
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
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
        parent::__construct( $fieldHelper, $translationHelper );

        $this->provider = $provider;
        $this->contentTypeService = $contentTypeService;
        $this->requestStack = $requestStack;
        $this->logger = $logger instanceof LoggerInterface ? $logger : new NullLogger();
    }

    /**
     * Returns if this field type handler supports current field
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     *
     * @return bool
     */
    protected function supports( Field $field )
    {
        return $field->value instanceof Value;
    }

    /**
     * Returns the field value, converted to string
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param string $tagName
     * @param array $params
     *
     * @throws FieldEmptyException
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function getFieldValue(Field $field, $tagName, array $params = array())
    {
        if ($this->fieldHelper->isFieldEmpty($this->content, $params[0])) {
            throw new FieldEmptyException($field->fieldDefIdentifier);
        }

        try {
            $media = $this->translationHelper->getTranslatedField($this->content, $params[0])->value;

            if (!isset($params[1])) {
                return $media->secure_url;
            }

            $contentTypeIdentifier = $this->contentTypeService->loadContentType(
                $this->content->contentInfo->contentTypeId
            )->identifier;
            $variation = $this->provider->buildVariation($media, $contentTypeIdentifier, $params[1]);

            return $variation->url;
        } catch (Exception $e) {
            $this->logger->error(
                "Open Graph keymedia handler: Error while getting image with id {$field->value->id}: " . $e->getMessage()
            );
        }

        throw new FieldEmptyException($field->fieldDefIdentifier);
    }

    /**
     * Returns fallback value.
     *
     * @param string $tagName
     * @param array $params
     *
     * @return string
     */
    protected function getFallbackValue($tagName, array $params = array())
    {
        if (!empty($params[2]) && ($request = $this->requestStack->getCurrentRequest()) !== null) {
            return $request->getUriForPath('/' . ltrim($params[2], '/'));
        }

        return '';
    }
}

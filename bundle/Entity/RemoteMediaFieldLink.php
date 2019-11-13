<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Entity;

class RemoteMediaFieldLink
{
    /**
     * @var int
     */
    protected $contentId;

    /**
     * @var int
     */
    protected $fieldId;

    /**
     * @var int
     */
    protected $versionId;

    /**
     * @var string
     */
    protected $resourceId;

    /**
     * @var string
     */
    protected $provider;

    /**
     * Sets eZ Publish content ID.
     *
     * @param mixed $contentId
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Entity\RemoteMediaFieldLink
     */
    public function setContentId($contentId)
    {
        $this->contentId = $contentId;

        return $this;
    }

    /**
     * Returns eZ Publish content ID.
     *
     * @return int
     */
    public function getContentId()
    {
        return $this->contentId;
    }

    /**
     * Sets eZ Publish field ID.
     *
     * @param mixed $fieldId
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Entity\RemoteMediaFieldLink
     */
    public function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;

        return $this;
    }

    /**
     * Returns eZ Publish field ID.
     *
     * @return mixed
     */
    public function getFieldId()
    {
        return $this->fieldId;
    }

    /**
     * @param mixed $versionId
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Entity\RemoteMediaFieldLink
     */
    public function setVersionId($versionId)
    {
        $this->versionId = $versionId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVersionId()
    {
        return $this->versionId;
    }

    /**
     * Sets remote resource id.
     *
     * @param string $resourceId
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Entity\RemoteMediaFieldLink
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    /**
     * Returns remote resource id.
     *
     * @return string
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * Sets remote provider identifier.
     *
     * @param string $provider
     *
     * @return \Netgen\Bundle\RemoteMediaBundle\Entity\RemoteMediaFieldLink
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Returns remote provider identifier.
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }
}

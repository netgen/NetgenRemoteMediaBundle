<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\ContentBrowser\Item;

use Netgen\ContentBrowser\Item\LocationInterface;

class Location implements LocationInterface
{
    protected $locationName;
    protected $locationId;
    protected $parentId;

    public function __construct($locationId, $locationName, $parentId)
    {
        $this->locationId = (string)$locationId;
        $this->locationName = $locationName;
        if ($parentId === null) {
            $this->parentId = null;
        } else {
            $this->parentId = (string)$parentId;
        }
    }

    public function getLocationId()
    {
        return $this->locationId;
    }

    public function getName()
    {
        return $this->locationName;
    }

    public function getParentId()
    {
        return $this->parentId;
    }
}

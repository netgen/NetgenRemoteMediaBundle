<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\ContentBrowser\Item;

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\ContentBrowser\Item\ItemInterface;

class Item implements ItemInterface
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value
     */
    protected $value;

    public function __construct(Value $value)
    {
        $this->value = $value;
    }

    /**
     * Returns the value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value->resourceId;
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->value->resourceId;
    }

    /**
     * Returns if the item is visible.
     *
     * @return bool
     */
    public function isVisible()
    {
        return true;
    }

    /**
     * Returns if the item is selectable.
     *
     * @return bool
     */
    public function isSelectable()
    {
        return true;
    }

    public function getRemoteValue()
    {
        return $this->value;
    }
}

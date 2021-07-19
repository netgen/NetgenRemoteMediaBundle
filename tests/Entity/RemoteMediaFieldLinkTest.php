<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Entity;

use Netgen\Bundle\RemoteMediaBundle\Entity\RemoteMediaFieldLink;
use PHPUnit\Framework\TestCase;

class RemoteMediaFieldLinkTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Entity\RemoteMediaFieldLink
     */
    protected $entity;

    protected function setUp()
    {
        $this->entity = new RemoteMediaFieldLink();
        parent::setUp();
    }

    public function testSettersAndGetters()
    {
        $this->entity
            ->setContentId(42)
            ->setFieldId(24)
            ->setVersionId(1)
            ->setResourceId('test')
            ->setProvider('testprovider');

        self::assertEquals(42, $this->entity->getContentId());
        self::assertEquals(24, $this->entity->getFieldId());
        self::assertEquals(1, $this->entity->getVersionId());
        self::assertEquals('test', $this->entity->getResourceId());
        self::assertEquals('testprovider', $this->entity->getProvider());
    }
}

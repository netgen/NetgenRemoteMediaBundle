<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Event\Cloudinary;

use Netgen\RemoteMedia\Event\Cloudinary\NotificationReceivedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(NotificationReceivedEvent::class)]
class NotificationReceivedEventTest extends TestCase
{
    public function test(): void
    {
        $request = new Request();
        $request->query->add(['timestamp' => '45657673457667']);
        $request->request->add(['type' => 'resource_uploaded']);

        $event = new NotificationReceivedEvent($request);

        self::assertSame(
            $request,
            $event->getRequest(),
        );

        self::assertSame(
            'ngrm.cloudinary.notification.received',
            $event::NAME,
        );
    }
}

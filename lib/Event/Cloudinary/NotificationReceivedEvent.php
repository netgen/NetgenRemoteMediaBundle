<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Event\Cloudinary;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

final class NotificationReceivedEvent extends Event
{
    public const NAME = 'ngrm.cloudinary.notification.received';

    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}

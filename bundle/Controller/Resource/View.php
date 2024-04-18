<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Resource;

use Netgen\RemoteMedia\API\ProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class View extends AbstractController
{
    public function __construct(
        private ProviderInterface $provider,
    ) {}

    public function __invoke(int $locationId, Request $request): Response
    {
        return new Response($this->renderView(
            $this->container->getParameter('netgen_remote_media.templates.view_resource'),
            [
                'location' => $this->provider->loadLocation($locationId),
                'css_class' => $request->query->get('css_class'),
                'variation_name' => $request->query->get('variation_name'),
                'variation_group' => $request->query->get('variation_group'),
                'alignment' => $request->query->get('alignment'),
            ]
        ));
    }
}

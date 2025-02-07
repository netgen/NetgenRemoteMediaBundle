<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Resource;

use Netgen\RemoteMedia\API\ProviderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig;

final class View
{
    public function __construct(
        private ProviderInterface $provider,
        private Twig\Environment $twig,
        private ParameterBagInterface $parameters,
    ) {}

    public function __invoke(int $locationId, Request $request): Response
    {
        return new Response($this->twig->render(
            $this->parameters->get('netgen_remote_media.templates.view_resource'),
            [
                'location' => $this->provider->loadLocation($locationId),
                'css_class' => $request->query->get('css_class'),
                'variation_name' => $request->query->get('variation_name'),
                'variation_group' => $request->query->get('variation_group'),
                'alignment' => $request->query->get('alignment'),
            ],
        ));
    }
}

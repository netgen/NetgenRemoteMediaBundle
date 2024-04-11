<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Controller\Resource;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class View extends AbstractController
{
    public function __invoke(int $locationId, Request $request): Response
    {
        // {% set css_class = params.css_class|default(null) %}
        // {% set resource_id = params.resource_id|default(null) %}
        // {% set resource_type = params.resource_type|default(null) %}
        // {% set coords = params.coords|default('[]') %}
        // {% set variation = params.variation|default(null) %}
        // {% set caption = params.caption|default(null) %}

        dump($this->container->getParameter('netgen_remote_media.templates.view_resource'));

        return new Response($this->renderView($this->container->getParameter('netgen_remote_media.templates.view_resource')));
    }
}

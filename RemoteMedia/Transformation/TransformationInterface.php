<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation;

interface TransformationInterface
{
    public function supports($options);

    /**
     * Takes options from the configuration and returns
     * properly configured array of options
     *
     * @param array $config
     *
     * @return array
     */
    public function process(array $config= array());
}

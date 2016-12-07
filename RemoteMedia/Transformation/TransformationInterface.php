<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Transformation;

interface TransformationInterface
{
    /**
     * If transformation is supported in the admin interface
     * for cropping.
     *
     * @return bool
     */
    public function isCroppable();

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

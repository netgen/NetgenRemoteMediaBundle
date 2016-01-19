<?php

class NgRemoteMedia
{
    public $public_id = null;
    public $version = null;
    public $width = null;
    public $height = null;
    public $format = null;
    public $url = null;
    public $secure_url = null;
    public $input_uri = null;
    public $resource_type = null;
    public $created_at = null;
    public $tags = array();
    public $original_filename = null;
    public $signature = null;
    public $bytes = null;
    public $type = null;
    public $etag = null;
    public $overwritten = null;

    /**
     * Returns an array with attributes that are available
     *
     * @return array
     */
    public function attributes()
    {
        return array(
            'public_id',
            'width',
            'height',
            'format',
            'url',
            'secure_url',
            'resource_type',
            'tags'
        );
    }

    /**
     * Method returns string interpretation of sylius product datatype
     *
     * @return string
     */
    public function toString()
    {
        return $this->public_id . '|#' .
            $this->url . '|#' .
            $this->secure_url . '|#';
    }

    /**
     * Returns true if the provided attribute exists
     *
     * @param string $name
     * @return bool
     */
    public function hasAttribute( $name )
    {
        return in_array( $name, $this->attributes() );
    }
    /**
     * Returns the specified attribute
     *
     * @param string $name
     * @return mixed
     */
    public function attribute( $name )
    {
        if ( empty( $this->public_id ) )
        {
            return null;
        }
        if ( $name == 'public_id' )
        {
            return $this->public_id;
        }
        if ( $name == 'url' )
        {
            return $this->url;
        }
        if ( $name == 'secure_url' )
        {
            return $this->secure_url;
        }
        if ( $name == 'width' )
        {
            return $this->width;
        }
        if ( $name == 'height' )
        {
            return $this->height;
        }
        if ( $name == 'format' )
        {
            return $this->format;
        }
        if ( $name == 'resource_type' )
        {
            return $this->resource_type;
        }
        if ( $name == 'tags' )
        {
            return $this->tags;
        }

        eZDebug::writeError( "Attribute '$name' does not exist", "NgRemoteMedia::attribute" );

        return null;
    }
}

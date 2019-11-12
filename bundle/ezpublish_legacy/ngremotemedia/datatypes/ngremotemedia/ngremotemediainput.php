<?php

class NgRemoteMediaInput
{
    /** @var string  */
    private $resourceId;

    /** @var array  */
    private $tags = [];

    /** @var string  */
    private $altText;

    /** @var array  */
    private $variations = [];

    /** @var  */
    private $newFile;

    /**
     * NgRemoteMediaInput constructor.
     *
     * @param $resourceId
     * @param $tags
     * @param $altText
     * @param $variations
     * @param $newFile
     */
    public function __construct(
        string $resourceId,
        array $tags = [],
        string $altText = '',
        array $variations = [],
        $newFile = null
    ) {
        $this->resourceId = $resourceId;
        $this->tags = $tags;
        $this->altText = $altText;
        $this->variations = $variations;
        $this->newFile = $newFile;
    }

    public static function fromEzHttp(eZHTTPTool $http, $base, $attributeId): NgRemoteMediaInput
    {
        $resourceId = $http->variable($base . '_media_id_' . $attributeId);
        $alttext =  $http->variable($base . '_alttext_' . $attributeId, '');
        $tags = $http->variable($base.'_tags_'.$attributeId, array());
        $variations = $http->variable($base.'_image_variations_'.$attributeId, array());
        $variations = json_decode($variations, true);

        //$file = eZHTTPFile::fetch($base.'_new_file_'.$attributeId );

        return new NgRemoteMediaInput($resourceId, $tags, $alttext, $variations, null);
    }

    /**
     * @return string
     */
    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @return string
     */
    public function getAltText(): string
    {
        return $this->altText;
    }

    /**
     * @return array
     */
    public function getVariations(): array
    {
        return $this->variations;
    }

    /**
     * @return null
     */
    public function getNewFile()
    {
        return $this->newFile;
    }
}

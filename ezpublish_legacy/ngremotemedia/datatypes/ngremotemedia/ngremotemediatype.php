<?php

use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;

class NgRemoteMediaType extends eZDataType
{
	const DATA_TYPE_STRING = 'ngremotemedia';
    const FIELD_FORMATS = 'data_text1';
    const FIELD_VALUE = 'data_text';

    /**
     * Construction of the class, note that the second parameter in eZDataType
     * is the actual name showed in the datatype dropdown list.
     */
    function __construct()
    {
        parent::__construct(
            self::DATA_TYPE_STRING,
            ezpI18n::tr( 'extension/ngremotemedia/datatypes', 'Remote Media' ),
            array(
                'serialize_supported' => true
            )
        );
    }

    /**
     * Called when the datatype is added to a content class
     *
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentClassAttribute $classAttribute
     */
    public function fetchClassAttributeHTTPInput($http, $base, $class)
    {
        $versionsKey = $base . '_versions_' . $class->attribute('id');
        if ($http->hasPostVariable($versionsKey))
        {
            $versions = explode("\n", $http->variable($versionsKey, ''));
            $versions = array_filter($versions);
            $versions = array_map('trim', $versions);
            $versions_array = array();

            foreach($versions as $version) {
                $tmp = explode(',', $version);
                $versions_array[$tmp[0]] = $tmp[1];
            }

            $json = json_encode($versions_array);
            $class->setAttribute(self::FIELD_FORMATS, $json);
        }
        return true;
    }

    /**
     * Called on {$class_attribute.content} in content class template
     *
     * @return array
     */
    public function classAttributeContent($class)
    {
        $lines = array();
        $formats = "";
        $versions = json_decode($class->attribute(self::FIELD_FORMATS), true);
        if (!empty($versions) && is_array($versions)) {
            foreach($versions as $version_name => $version) {
                $lines[] = $version_name . ',' . $version;
            }
            $formats = join("\n", $lines);
        }
        return $formats;
    }

    /**
     * Validations for when a ContentObject containing this datatype
     * as an attribute is saved
     *
     * @param eZHTTPTool $http
     * @param mixed $base
     * @param object $contentObjectAttribute
     *
     * @return bool
     */
    public function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        return eZInputValidator::STATE_ACCEPTED;
    }

    /*!
    */
    public function fetchObjectAttributeHTTPInput( $http, $base, $attribute )
    {
        // Get value of connected media id
        $attributeId = $attribute->attribute('id');
        $data = array(
            'id' =>  $http->variable($base . '_media_id_' . $attributeId)
        );

        $extras = $http->variable($base . '_data_' . $attributeId);
        if ($extras) {
            $data += json_decode($extras, true);
        }
        $data['alttext'] = $http->variable($base . '_alttext_' . $attributeId, '');

        $handler = new Handler($attribute);

        return $data['id'] ? $handler->setMedia($data) : $handler->remove();
    }

    /**
     * Check if attribute has content
     * Called before {$attribute.content} in templates delegates to
     * `objectAttributeContent` to actuall fetch the content
     *
     * @param object $attribute
     * @return bool
     */
    public function hasObjectAttributeContent($attribute)
    {
        $value = $this->objectAttributeContent($attribute);
        return !empty($value->resourceId);
    }

    /**
     * Fetch content contained in this attribute when its stored
     * This method is triggered when a template states {$attribute.content}
     *
     * @param object $attribute
     * @return \remotemedia\models\media\Handler
     */
    public function objectAttributeContent($attribute)
    {
        $value = new Value(json_decode($attribute->DataText), true);
        return $value;
    }

    public function onPublish($attribute, $contentObject, $publishedNodes)
    {
        $handler = $this->objectAttributeContent($attribute);
        $handler->reportUsage($contentObject);
    }
}

eZDataType::register(
    NgRemoteMediaType::DATA_TYPE_STRING,
    'NgRemoteMediaType'
);

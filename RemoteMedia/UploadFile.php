<?php

namespace Netgen\Bundle\RemoteMediaBundle\RemoteMedia;

use \eZHTTPFile;
use eZ\Publish\Core\FieldType\Image\Value;

class UploadFile
{
    private $uri;

    private $originalFilename;

    private $originalExtension;

    private function __construct() {}

    /**
     * Constructs UploadFile from given uri.
     *
     * @param $uri
     *
     * @return UploadFile
     */
    public static function fromUri($uri)
    {
        $uploadFile = new UploadFile;

        $uploadFile->uri = $uri;
        $uploadFile->originalFilename = pathinfo($uri, PATHINFO_FILENAME);
        $uploadFile->originalExtension = pathinfo($uri, PATHINFO_EXTENSION);

        return $uploadFile;
    }

    /**
     * Constructs UploadFile from given eZHTTPFile (usually uploaded through the legacy admin).
     *
     * @param eZHTTPFile $file
     *
     * @return UploadFile
     */
    public static function fromZHTTPFile(eZHTTPFile $file)
    {
        $uploadFile = new UploadFile;

        $uploadFile->uri = $file->Filename;
        $uploadFile->originalFilename = pathinfo($file->OriginalFilename, PATHINFO_FILENAME);
        $uploadFile->originalExtension = pathinfo($file->OriginalFilename, PATHINFO_EXTENSION);

        return $uploadFile;
    }

    /**
     * Constructs UploadFile from given eZImage field Value.
     *
     * @param Value $value
     * @param $webRoot
     *
     * @return UploadFile
     */
    public static function fromEzImageValue(Value $value, $webRoot)
    {
        $uploadFile = new UploadFile;

        $uploadFile->uri = $webRoot.$value->uri;
        $uploadFile->originalFilename = pathinfo($value->fileName, PATHINFO_FILENAME);
        $uploadFile->originalExtension = pathinfo($value->fileName, PATHINFO_EXTENSION);

        return $uploadFile;
    }

    /**
     * @return string
     */
    public function uri()
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function originalFilename()
    {
        return $this->originalFilename;
    }

    /**
     * @return string
     */
    public function originalExtension()
    {
        return $this->originalExtension;
    }
}

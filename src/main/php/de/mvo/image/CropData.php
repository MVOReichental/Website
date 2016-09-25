<?php
namespace de\mvo\image;

use stdClass;

class CropData
{
    /**
     * @var int|null
     */
    public $x = 0;
    /**
     * @var int|null
     */
    public $y = 0;
    /**
     * @var int|null
     */
    public $width;
    /**
     * @var int|null
     */
    public $height;

    /**
     * @param stdClass $object
     * @return CropData
     */
    public static function readFromObject(stdClass $object)
    {
        $cropData = new self;

        $cropData->x = isset($object->x) ? $object->x : 0;
        $cropData->y = isset($object->y) ? $object->y : 0;
        $cropData->width = isset($object->width) ? $object->width : null;
        $cropData->height = isset($object->height) ? $object->height : null;

        return $cropData;
    }

    /**
     * Set the width and height from the image.
     * @param resource $image
     */
    public function setFromImage($image)
    {
        $this->width = imagesx($image);
        $this->height = imagesy($image);
    }

    /**
     * Validate the crop data.
     *
     * @return bool true if valid, false otherwise
     */
    public function validate()
    {
        if (!$this->width or $this->width < 0) {
            return false;
        }

        if (!$this->height or $this->height < 0) {
            return false;
        }

        return true;
    }
}
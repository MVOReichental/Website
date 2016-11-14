<?php
namespace de\mvo\image;

class Image
{
    /**
     * @var resource
     */
    private $image;

    /**
     * @param resource $image The image resource to use
     */
    public function __construct($image)
    {
        $this->image = $image;
    }

    /**
     * Get the width of the image.
     *
     * @return int
     */
    public function getWidth()
    {
        return imagesx($this->image);
    }

    /**
     * Get the height of the image.
     *
     * @return int
     */
    public function getHeight()
    {
        return imagesy($this->image);
    }

    /**
     * Crop and resize the image to the specified size.
     *
     * Note: This will create a copy of the current image.
     *
     * @param int $maxWidth The maximum width of the new image
     * @param int $maxHeight The maximum height of the new image
     * @param CropData $cropData The crop data to use
     * @return bool true on success, false on error
     */
    public function crop($maxWidth, $maxHeight, CropData $cropData)
    {
        self::calculateResize($cropData->width, $cropData->height, $maxWidth, $maxHeight, $newWidth, $newHeight);

        $croppedImage = imagecreatetruecolor($newWidth, $newHeight);
        if ($croppedImage === false) {
            return false;
        }

        if (!imagecopyresampled($croppedImage, $this->image, 0, 0, $cropData->x, $cropData->y, $newWidth, $newHeight, $cropData->width, $cropData->height)) {
            return false;
        }

        $this->image = $croppedImage;

        return true;
    }

    /**
     * Get the internal image resource which is currently used.
     *
     * @return resource
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Save the image as BMP.
     *
     * @param string|null $filename The file to which the image should be written to. If not set or null, the raw image stream will be outputted directly.
     * @param int|null $foreground
     * @return bool true on success, false on error
     */
    public function saveAsBmp($filename = null, $foreground = null)
    {
        return imagewbmp($this->image, $filename, $foreground);
    }

    /**
     * Save the image as GIF.
     *
     * @param string|null $filename The file to which the image should be written to. If not set or null, the raw image stream will be outputted directly.
     * @return bool true on success, false on error
     */
    public function saveAsGif($filename = null)
    {
        return imagegif($this->image, $filename);
    }

    /**
     * Save the image as JPEG.
     *
     * @param string|null $filename The file to which the image should be written to. If not set or null, the raw image stream will be outputted directly.
     * @param int|null $quality
     * @return bool true on success, false on error
     */
    public function saveAsJpeg($filename = null, $quality = null)
    {
        return imagejpeg($this->image, $filename, $quality);
    }

    /**
     * Save the image as PNG.
     *
     * @param string|null $filename The file to which the image should be written to. If not set or null, the raw image stream will be outputted directly.
     * @param int|null $quality
     * @param int|null $filters
     * @return bool true on success, false on error
     */
    public function saveAsPng($filename = null, $quality = null, $filters = null)
    {
        return imagepng($this->image, $filename, $quality, $filters);
    }

    /**
     * Calculate the size for the specified original size to fit into the specified target size.
     *
     * @param int $originalWidth The original width
     * @param int $originalHeight The original height
     * @param int $maxWidth The maximum width
     * @param int $maxHeight The maximum height
     * @param int $newWidth The calculated width
     * @param int $newHeight The calculated height
     */
    public static function calculateResize($originalWidth, $originalHeight, $maxWidth, $maxHeight, &$newWidth, &$newHeight)
    {
        if ($originalWidth <= $maxWidth and $originalHeight <= $maxHeight) {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        } else {
            $ratio = $maxWidth / $originalWidth;

            $newWidth = $maxWidth;
            $newHeight = $originalHeight * $ratio;

            if ($newHeight > $maxHeight) {
                $ratio = $maxHeight / $originalHeight;

                $newHeight = $maxHeight;
                $newWidth = $originalWidth * $ratio;
            }
        }

        $newWidth = (int)$newWidth;
        $newHeight = (int)$newHeight;
    }
}
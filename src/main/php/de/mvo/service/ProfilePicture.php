<?php
namespace de\mvo\service;

use de\mvo\model\users\User;
use de\mvo\utils\Image;
use stdClass;

class ProfilePicture extends AbstractService
{
    public function get()
    {
        if (!isset($_GET["hash"])) {
            http_response_code(400);
            echo "Missing 'hash' parameter";
            return null;
        }

        $filename = User::getProfilePicturePath($this->params->id);

        if (md5_file($filename) != $_GET["hash"]) {
            http_response_code(404);
            echo "File not found";
            return null;
        }

        header("Content-Type: image/jpeg");
        readfile($filename);
        return null;
    }

    public function upload()
    {
        $user = User::getCurrent();

        if ($this->params->id != $user->id) {
            http_response_code(403);
            echo "DIFFERENT_USER_ID_UPDATE";
            return null;
        }

        if (!isset($_FILES["file"])) {
            http_response_code(400);
            echo "MISSING_FILE";
            return null;
        }

        $newImage = null;

        switch ($_FILES["file"]["error"]) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                http_response_code(400);
                echo "FILE_SIZE_EXCEEDED";
                return null;
            default:
                error_log("Upload error (error " . $_FILES["file"]["error"] . ")");

                http_response_code(500);
                echo "UPLOAD_ERROR";
                return null;
        }

        $sourceImage = imagecreatefromjpeg($_FILES["file"]["tmp_name"]);

        if (isset($_POST["crop"])) {
            $cropData = json_decode($_POST["crop"]);
            if ($cropData === null or !isset($cropData->width) or !isset($cropData->height) or $cropData->width <= 0 or $cropData->height <= 0) {
                http_response_code(400);
                echo "INVALID_CROP_DATA";
                return null;
            }

            if (!isset($cropData->x)) {
                $cropData->x = 0;
            }

            if (!isset($cropData->y)) {
                $cropData->y = 0;
            }
        } else {
            $cropData = new stdClass;

            $cropData->x = 0;
            $cropData->y = 0;
            $cropData->w = imagesx($sourceImage);
            $cropData->w = imagesy($sourceImage);
        }

        Image::calculateResize($cropData->width, $cropData->height, 600, 600, $width, $height);

        $croppedImage = imagecreatetruecolor($width, $height);
        if ($croppedImage and imagecopyresampled($croppedImage, $sourceImage, 0, 0, $cropData->x, $cropData->y, $width, $height, $cropData->width, $cropData->height)) {
            $filename = PROFILE_PICTURES_ROOT . "/" . User::getCurrent()->id . ".jpg";
            if (imagejpeg($croppedImage, $filename)) {
                echo "OK";
                return null;
            } else {
                error_log("Unable to save file to " . $filename);
            }
        } else {
            error_log("Unable to crop/resize image");
        }

        http_response_code(500);
        echo "IMAGE_PROCESSING_ERROR";
        return null;
    }
}
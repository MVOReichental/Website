<?php
namespace de\mvo\service;

use de\mvo\image\CropData;
use de\mvo\image\Image;
use de\mvo\model\users\User;

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
        $currentUser = User::getCurrent();

        if ($this->params->id == $currentUser->id) {
            $user = $currentUser;
        } else {
            if ($currentUser->hasPermission("admin.userManagement")) {
                $user = User::getById($this->params->id);
            } else {
                http_response_code(403);
                echo "DIFFERENT_USER_ID_UPDATE";
                return null;
            }
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

        $sourceImage = imagecreatefromstring(file_get_contents($_FILES["file"]["tmp_name"]));

        if ($sourceImage === false) {
            http_response_code(400);
            echo "INVALID_FORMAT";
            return null;
        }

        if (isset($_POST["crop"])) {
            $cropData = json_decode($_POST["crop"]);
            if ($cropData === null) {
                $cropData = new CropData;
                $cropData->setFromImage($sourceImage);
            } else {
                $cropData = CropData::readFromObject($cropData);
            }
        } else {
            $cropData = new CropData;
            $cropData->setFromImage($sourceImage);
        }

        if (!$cropData->validate()) {
            http_response_code(400);
            echo "INVALID_CROP_DATA";
            return null;
        }

        $image = new Image($sourceImage);

        if ($image->crop(600, 600, $cropData)) {
            $filename = PROFILE_PICTURES_ROOT . "/" . $user->id . ".jpg";
            if ($image->saveAsJpeg($filename, 75)) {
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
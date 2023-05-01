<?php
namespace App\Controller;

use App\image\CropData;
use App\image\Image;
use App\Entity\users\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(name: "ProfilePicture::", methods: ["GET"])]
class ProfilePicture extends AbstractController
{
    #[Route("/users/{id}/profile-picture", requirements: ["id" => "\d+"])]
    public function get(int $id): Response
    {
        if (!isset($_GET["hash"])) {
            throw new BadRequestHttpException("Missing 'hash' parameter");
        }

        $filename = User::getProfilePicturePath($id);

        if (md5_file($filename) != $_GET["hash"]) {
            throw new NotFoundHttpException("File not found");
        }

        return $this->file($filename);
    }

    #[Route("/users/{id}/profile-picture", requirements: ["id" => "\d+"], methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED")]
    public function upload(int $id): Response
    {
        $currentUser = User::getCurrent();

        if ($id == $currentUser->id) {
            $user = $currentUser;
        } else {
            if ($currentUser->hasPermission("admin.userManagement")) {
                $user = User::getById($id);
            } else {
                throw new AccessDeniedHttpException("DIFFERENT_USER_ID_UPDATE");
            }
        }

        if (!isset($_FILES["file"])) {
            throw new BadRequestHttpException("MISSING_FILE");
        }

        $newImage = null;

        switch ($_FILES["file"]["error"]) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
                throw new IniSizeFileException;
            case UPLOAD_ERR_FORM_SIZE:
                throw new FormSizeFileException;
            default:
                error_log("Upload error (error " . $_FILES["file"]["error"] . ")");

                throw new UploadException;
        }

        $sourceImage = imagecreatefromstring(file_get_contents($_FILES["file"]["tmp_name"]));

        if ($sourceImage === false) {
            throw new BadRequestHttpException("INVALID_FORMAT");
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
            throw new BadRequestHttpException("INVALID_CROP_DATA");
        }

        $image = new Image($sourceImage);

        if ($image->crop(600, 600, $cropData)) {
            $filename = PROFILE_PICTURES_ROOT . "/" . $user->id . ".jpg";
            if ($image->saveAsJpeg($filename, 75)) {
                return new Response("OK");
            } else {
                error_log("Unable to save file to " . $filename);
            }
        } else {
            error_log("Unable to crop/resize image");
        }

        throw new BadRequestHttpException("IMAGE_PROCESSING_ERROR");
    }
}
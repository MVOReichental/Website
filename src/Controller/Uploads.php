<?php
namespace App\Controller;

use App\Controller\exception\NotFoundException;
use App\Entity\uploads\Upload;
use App\uploadhandler\File;
use App\uploadhandler\Files;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Uploads extends AbstractController
{
    /**
     * @throws NotFoundException
     */
    public function get()
    {
        $upload = Upload::getById($this->params->id);
        if ($upload === null) {
            throw new NotFoundException;
        }

        if ($upload->key != $this->params->key) {
            throw new NotFoundException;
        }

        if (!$upload->stream()) {
            throw new NotFoundException;
        }
    }

    public function upload()
    {
        $files = new Files($_FILES["upload"]);

        if (count($files) > 1) {
            http_response_code(413);
            return null;
        }

        /**
         * @var $file File
         */
        $file = $files[0];

        switch ($file->error) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_FORM_SIZE:
            case UPLOAD_ERR_INI_SIZE:
                http_response_code(413);
                return null;
            default:
                http_response_code(500);
                return null;
        }

        $upload = Upload::add($file->tempName, $file->name);

        if ($upload === null) {
            http_response_code(500);
            return null;
        }

        return json_encode(array
        (
            "uploaded" => true,
            "fileName" => $upload->filename,
            "url" => $upload->getUrl()
        ));
    }
}
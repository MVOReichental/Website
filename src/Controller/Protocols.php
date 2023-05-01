<?php
namespace App\Controller;

use App\Date;
use App\Entity\protocols\Groups;
use App\Entity\protocols\Protocol;
use App\Entity\protocols\ProtocolsList;
use App\Entity\users\Groups as UserGroups;
use App\Entity\users\User;
use App\TwigRenderer;
use App\uploadhandler\File;
use App\uploadhandler\Files;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Error\Error;

class Protocols extends AbstractController
{
    /**
     * @return string
     * @throws Error
     */
    public function getList()
    {
        if (isset($_GET["uploaded"])) {
            $uploadedState = "ok";
        } elseif (isset($_GET["failed"])) {
            $uploadedState = "failed";
        } else {
            $uploadedState = null;
        }

        return TwigRenderer::render("protocols/page", array
        (
            "uploaded" => $uploadedState,
            "groups" => UserGroups::getAll(),
            "allowUpload" => User::getCurrent()->hasPermission("protocols.upload.*"),
            "protocols" => ProtocolsList::get()->getVisibleForUser(User::getCurrent())
        ));
    }

    /**
     * @return string
     * @throws Error
     */
    public function showUploadForm()
    {
        return TwigRenderer::render("protocols/upload", array
        (
            "groups" => UserGroups::getAll()
        ));
    }

    /**
     * @return null
     * @throws Error
     */
    public function upload()
    {
        $files = new Files($_FILES["file"]);

        if ($files->offsetExists(0)) {
            /**
             * @var $file File
             */
            $file = $files->offsetGet(0);
            if ($file->error == UPLOAD_ERR_OK) {
                $upload = $file->createUpload();
                if ($upload !== null) {
                    $protocol = new Protocol;

                    $protocol->date = new Date($_POST["date"]);
                    $protocol->title = $_POST["title"];
                    $protocol->uploader = User::getCurrent();
                    $protocol->upload = $upload;

                    $protocol->groups = new Groups;

                    if (isset($_POST["groups"]) and is_array($_POST["groups"])) {
                        foreach ($_POST["groups"] as $group) {
                            if (!UserGroups::hasGroup($group)) {
                                continue;
                            }

                            $protocol->groups->append($group);
                        }
                    }

                    $protocol->save();
                    $protocol->sendMail();

                    header("Location: /internal/protocols?uploaded");
                    return null;
                }
            }
        }

        header("Location: /internal/protocols?failed");
        return null;
    }
}
<?php
namespace de\mvo\service;

use de\mvo\Date;
use de\mvo\model\protocols\Groups;
use de\mvo\model\protocols\Protocol;
use de\mvo\model\protocols\ProtocolsList;
use de\mvo\model\users\User;
use de\mvo\TwigRenderer;
use de\mvo\uploadhandler\File;
use de\mvo\uploadhandler\Files;
use Twig_Error;

class Protocols extends AbstractService
{
    /**
     * @return string
     * @throws Twig_Error
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
            "groups" => Groups::getAll(),
            "allowUpload" => User::getCurrent()->hasPermission("protocols.upload.*"),
            "protocols" => ProtocolsList::get()->getVisibleForUser(User::getCurrent())
        ));
    }

    /**
     * @return string
     * @throws Twig_Error
     */
    public function showUploadForm()
    {
        return TwigRenderer::render("protocols/upload", array
        (
            "groups" => Groups::getAll()
        ));
    }

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
                    $protocol->upload = $upload;

                    $protocol->groups = new Groups;

                    if (isset($_POST["groups"]) and is_array($_POST["groups"])) {
                        foreach ($_POST["groups"] as $group) {
                            if (!isset(Groups::getAll()[$group])) {
                                continue;
                            }

                            $protocol->groups->append($group);
                        }
                    }

                    $protocol->save();

                    header("Location: /internal/protocols?uploaded");
                    return null;
                }
            }
        }

        header("Location: /internal/protocols?failed");
        return null;
    }
}
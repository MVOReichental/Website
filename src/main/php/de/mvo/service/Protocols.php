<?php
namespace de\mvo\service;

use de\mvo\model\protocols\Groups;
use de\mvo\model\protocols\Protocol;
use de\mvo\model\protocols\ProtocolsList;
use de\mvo\model\users\User;
use de\mvo\TwigRenderer;
use de\mvo\uploadhandler\File;
use de\mvo\uploadhandler\Files;

class Protocols extends AbstractService
{
    public function getList($uploaded = null)
    {
        return TwigRenderer::render("protocols/page", array
        (
            "uploaded" => $uploaded,
            "groups" => Groups::getAll(),
            "allowUpload" => User::getCurrent()->hasPermission("protocols.upload.*"),
            "protocols" => ProtocolsList::get()->getVisibleForUser(User::getCurrent())
        ));
    }

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

                    $protocol->date = $_POST["date"];
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

                    return $this->getList(array
                    (
                        "ok" => true
                    ));
                }
            }
        }

        return $this->getList(array
        (
            "ok" => false
        ));
    }
}
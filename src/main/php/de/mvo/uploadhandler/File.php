<?php
namespace de\mvo\uploadhandler;

use de\mvo\model\uploads\Upload;

class File
{
    public $name;
    public $type;
    public $size;
    public $tempName;
    public $error;

    public function createUpload()
    {
        return Upload::add($this->tempName, $this->name);
    }
}
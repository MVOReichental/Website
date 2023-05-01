<?php
namespace App\uploadhandler;

use ArrayObject;

class Files extends ArrayObject
{
    /**
     * @var array
     */
    private $item;

    /**
     * @param array $item One item of the $_FILES array
     */
    public function __construct(array $item)
    {
        parent::__construct();

        $this->item = $item;

        if (is_array($this->item["name"])) {
            $this->handleMultipleFiles();
        } else {
            $this->handleSingleFile();
        }
    }

    private function handleMultipleFiles()
    {
        foreach (array_keys($this->item["name"]) as $index) {
            $file = new File;

            $file->name = $this->item["name"][$index];
            $file->type = $this->item["type"][$index];
            $file->size = $this->item["size"][$index];
            $file->tempName = $this->item["tmp_name"][$index];
            $file->error = $this->item["error"][$index];

            $this->append($file);
        }
    }

    private function handleSingleFile()
    {
        $file = new File;

        $file->name = $this->item["name"];
        $file->type = $this->item["type"];
        $file->size = $this->item["size"];
        $file->tempName = $this->item["tmp_name"];
        $file->error = $this->item["error"];

        $this->append($file);
    }

    public function append($file)
    {
        if ($file->error == UPLOAD_ERR_NO_FILE) {
            return;
        }

        parent::append($file);
    }
}
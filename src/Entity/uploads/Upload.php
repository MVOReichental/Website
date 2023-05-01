<?php
namespace App\Entity\uploads;

use PDO;
use App\Database;

class Upload
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $key;
    /**
     * @var string
     */
    public $filename;

    public function __construct()
    {
        if ($this->id === null) {
            return;
        }

        $this->id = (int)$this->id;
    }

    /**
     * @param int $id
     *
     * @return Upload|null
     */
    public static function getById($id)
    {
        $query = Database::prepare("
            SELECT *
            FROM `uploads`
            WHERE `id` = :id
        ");

        $query->bindValue(":id", $id, PDO::PARAM_INT);

        $query->execute();

        if (!$query->rowCount()) {
            return null;
        }

        return $query->fetchObject(self::class);
    }

    public static function add($sourcePath, $filename)
    {
        $upload = new Upload;

        $upload->key = substr(md5_file($sourcePath), 0, 8);
        $upload->filename = $filename;

        Database::pdo()->beginTransaction();

        $upload->saveAsNew();

        if (!rename($sourcePath, $upload->getAbsoluteFilePath())) {
            Database::pdo()->rollBack();

            return null;
        }

        Database::pdo()->commit();

        return $upload;
    }

    public function getAbsoluteFilePath()
    {
        return UPLOADS_ROOT . "/" . $this->id;
    }

    public function getUrl()
    {
        return sprintf("/uploads/%d/%s/%s", $this->id, $this->key, $this->filename);
    }

    public function saveAsNew()
    {
        $query = Database::prepare("
            INSERT INTO `uploads`
            SET
                `key` = :key,
                `filename` = :filename
        ");

        $query->bindValue(":key", $this->key);
        $query->bindValue(":filename", $this->filename);

        $query->execute();

        $this->id = (int)Database::lastInsertId();
    }

    public function stream()
    {
        $filename = $this->getAbsoluteFilePath();

        $file = fopen($filename, "r");
        if ($file === false) {
            return false;
        } else {
            header("Content-Type: application/octet-stream");
            header("Content-Length: " . filesize($filename));
            header("Content-Transfer-Encoding: chunked");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");

            while ($chunk = fread($file, 4096)) {
                echo $chunk;
            }

            fclose($file);

            return true;
        }
    }
}
<?php
namespace de\mvo\model\uploads;

use de\mvo\Database;

class Upload
{
	public $id;
	public $filename;

	public function __construct()
	{
		$this->id = (int) $this->id;
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

		$query->execute(array
		(
			":id" => $id
		));

		if (!$query->rowCount())
		{
			return null;
		}

		return $query->fetchObject(self::class);
	}

	public static function add($sourcePath, $filename)
	{
		$upload = new Upload;

		$upload->filename = $filename;

		Database::pdo()->beginTransaction();

		$query = Database::prepare("
			INSERT INTO `uploads`
			SET `filename` = :filename
		");

		$query->execute(array
		(
			":filename" => $filename
		));

		$upload->id = Database::lastInsertId();

		if (!rename($sourcePath, $upload->getAbsoluteFilePath()))
		{
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

	public function stream()
	{
		$filename = $this->getAbsoluteFilePath();

		$file = fopen($filename, "r");
		if ($file === false)
		{
			return false;
		}
		else
		{
			header("Content-Type: application/octet-stream");
			header("Content-Length: " . filesize($filename));
			header("Content-Transfer-Encoding: chunked");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Pragma: public");

			while ($chunk = fread($file, 4096))
			{
				echo $chunk;
			}

			fclose($file);

			return true;
		}
	}
}
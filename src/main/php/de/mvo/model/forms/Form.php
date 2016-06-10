<?php
namespace de\mvo\model\forms;

use de\mvo\Database;
use de\mvo\Date;
use de\mvo\utils\File;

class Form
{
	public $id;
	public $filename;
	public $name;
	public $title;

	public function __construct()
	{
		$this->id = (int) $this->id;
	}

	/**
	 * @param string $filename
	 *
	 * @return Form|null
	 */
	public static function getByFilename($filename)
	{
		$query = Database::prepare("
			SELECT *
			FROM `forms`
			WHERE `filename` = :filename
		");

		$query->execute(array
		(
			":filename" => $filename
		));

		if (!$query->rowCount())
		{
			return null;
		}

		return $query->fetchObject(self::class);
	}

	public function getAbsoluteFilePath()
	{
		return RESOURCES_ROOT . "/forms/" . basename($this->filename);
	}

	public function type()
	{
		return File::getType(pathinfo($this->filename, PATHINFO_EXTENSION));
	}

	public function date()
	{
		$file = $this->getAbsoluteFilePath();
		if (!file_exists($file))
		{
			return null;
		}

		$date = new Date;

		$date->setTimestamp(filemtime($file));

		return $date;
	}

	public function stream()
	{
		$filename = $this->getAbsoluteFilePath();

		$file = fopen($filename, "r");
		if ($file == false)
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
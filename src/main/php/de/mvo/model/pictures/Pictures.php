<?php
namespace de\mvo\model\pictures;

use ArrayObject;
use de\mvo\Database;

class Pictures extends ArrayObject
{
	/**
	 * @param int $albumId
	 */
	public function __construct($albumId)
	{
		$query = Database::prepare("
			SELECT *
			FROM `pictures`
			WHERE `albumId` = :albumId
		");

		$query->execute(array
		(
			":albumId" => $albumId
		));

		while ($picture = $query->fetchObject(Picture::class))
		{
			$this->append($picture);
		}
	}

	public function getPictureById($id)
	{
		/**
		 * @var $picture Picture
		 */
		foreach ($this as $picture)
		{
			if ($picture->id == $id)
			{
				return $picture;
			}
		}

		return null;
	}
}
<?php
namespace de\mvo\model\notedirectory;

use ArrayObject;
use de\mvo\Database;

class Categories extends ArrayObject
{
	public static function getAll()
	{
		$query = Database::query("
			SELECT *
			FROM `notedirectorycategories`
			ORDER BY `order` ASC
		");

		$categories = new self;

		while ($category = $query->fetchObject(Category::class))
		{
			$categories->append($category);
		}

		return $categories;
	}
}
<?php
namespace de\mvo\model\notedirectory;

use ArrayObject;
use de\mvo\Database;

class Titles extends ArrayObject
{
	public static function getAll()
	{
		$query = Database::query("
			SELECT *
			FROM `notedirectorytitles`
			ORDER BY `title` ASC
		");

		$titles = new self;

		while ($title = $query->fetchObject(Title::class))
		{
			$titles->append($title);
		}

		return $titles;
	}

	public static function getByCategory(Category $category)
	{
		$query = Database::prepare("
			SELECT *
			FROM `notedirectorytitles`
			WHERE `categoryId` = :categoryId
			ORDER BY `title` ASC
		");

		$query->execute(array
		(
			":categoryId" => $category->id
		));

		$titles = new self;

		while ($title = $query->fetchObject(Title::class))
		{
			$titles->append($title);
		}

		return $titles;
	}

	public function getInCategories()
	{
		$categories = Categories::getAll();

		/**
		 * @var $category Category
		 */
		foreach ($categories as $category)
		{
			$category->titles = $this->getInCategory($category);
		}

		return $categories;
	}

	public function getInCategory(Category $category)
	{
		$titles = new self;

		/**
		 * @var $title Title
		 */
		foreach ($this as $title)
		{
			if ($title->category->isEqualTo($category))
			{
				$titles->append($title);
			}
		}

		return $titles;
	}

	public function getById($id)
	{
		/**
		 * @var $title Title
		 */
		foreach ($this as $title)
		{
			if ($title->id == $id)
			{
				return $title;
			}
		}

		return null;
	}

	public function first()
	{
		return $this->offsetGet(0);
	}
}
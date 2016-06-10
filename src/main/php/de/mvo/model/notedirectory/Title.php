<?php
namespace de\mvo\model\notedirectory;

use de\mvo\Database;

class Title
{
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var int
	 */
	public $number;
	/**
	 * @var string
	 */
	public $title;
	/**
	 * @var string
	 */
	public $composer;
	/**
	 * @var string
	 */
	public $arranger;
	/**
	 * @var string
	 */
	public $publisher;
	/**
	 * @var Category
	 */
	public $category;
	/**
	 * @var int
	 */
	private $categoryId;

	public function __construct()
	{
		if ($this->id === null)
		{
			return;
		}

		$this->id = (int) $this->id;
		$this->number = (int) $this->number;

		$query = Database::prepare("
			SELECT *
			FROM `notedirectorycategories`
			WHERE `id` = :id
		");

		$query->execute(array
		(
			":id" => $this->categoryId
		));

		if ($query->rowCount())
		{
			$this->category = $query->fetchObject(Category::class);
		}
	}
}
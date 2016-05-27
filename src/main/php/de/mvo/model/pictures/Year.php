<?php
namespace de\mvo\model\pictures;

class Year
{
	/**
	 * @var int
	 */
	public $year;
	/**
	 * @var Album
	 */
	public $coverAlbum;
	private $coverAlbumId;

	public function __construct()
	{
		$this->year = (int) $this->year;
		$this->coverAlbum = Album::getById($this->coverAlbumId);
	}
}
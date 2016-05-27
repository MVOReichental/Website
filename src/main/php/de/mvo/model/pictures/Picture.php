<?php
namespace de\mvo\model\pictures;

class Picture
{
	public $id;
	public $albumId;
	public $file;
	public $title;

	public function __construct()
	{
		$this->id = (int) $this->id;
		$this->albumId = (int) $this->albumId;
	}
}
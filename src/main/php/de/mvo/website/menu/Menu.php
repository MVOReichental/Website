<?php
namespace de\mvo\website\menu;

class Menu
{
	public function __construct($items)
	{
		foreach ($items as $item)
		{
			
		}
	}

	public static function buildTopLevel()
	{
		return new self(json_decode(file_get_contents(RESOURCES_ROOT . "/menu.json")));
	}
}
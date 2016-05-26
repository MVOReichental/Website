<?php
namespace de\mvo\renderer;

class StaticRenderer extends AbstractRenderer
{
	private $filename;

	public function __construct($filename)
	{
		$this->filename = $filename;
	}

	public function render()
	{
		return file_get_contents(VIEWS_ROOT . "/" . $this->filename . ".html");
	}
}
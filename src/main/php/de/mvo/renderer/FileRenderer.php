<?php
namespace de\mvo\renderer;

class FileRenderer extends AbstractRenderer
{
	private $filename;
	private $contentType;

	public function __construct($filename, $contentType)
	{
		$this->filename = $filename;
		$this->contentType = $contentType;
	}

	public function render()
	{
		header("Content-Type: " . $this->contentType);
		readfile($this->filename);
		return null;
	}
}
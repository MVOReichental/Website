<?php
namespace de\mvo\service;

class File extends AbstractService
{
	public function get($filename, $contentType)
	{
		header("Content-Type: " . $contentType);
		readfile($filename);
		return null;
	}
}
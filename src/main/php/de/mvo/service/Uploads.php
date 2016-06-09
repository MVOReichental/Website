<?php
namespace de\mvo\service;

use de\mvo\model\uploads\Upload;
use de\mvo\service\exception\NotFoundException;

class Uploads extends AbstractService
{
	public function get()
	{
		$upload = Upload::getById($this->params->id);
		if ($upload === null)
		{
			throw new NotFoundException;
		}

		if (!$upload->stream())
		{
			throw new NotFoundException;
		}
	}
}
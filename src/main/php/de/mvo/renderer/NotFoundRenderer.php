<?php
namespace de\mvo\renderer;

class NotFoundRenderer extends AbstractRenderer
{
	public function render()
	{
		http_response_code(404);
		return file_get_contents(VIEWS_ROOT . "/not-found.html");
	}
}
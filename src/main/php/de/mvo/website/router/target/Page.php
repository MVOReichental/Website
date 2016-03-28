<?php
namespace de\mvo\website\router\target;

use de\mvo\website\utils\HttpMethod;
use Mustache_Engine;

class Page extends AbstractTarget
{
	public $class;

	public function __construct($class)
	{
		$this->class = $class;
	}

	public function execute($method, $parameters)
	{
		$class = PAGE_NAMESPACE . "\\" . $this->class;

		/**
		 * @var $page \de\mvo\website\page\Page
		 */
		$page = new $class;

		$page->init();

		switch ($method)
		{
			case HttpMethod::GET:
				$page->get();
				break;
			case HttpMethod::POST:
				$page->post();
				break;
		}

		$page->endContent();

		$mustache = new Mustache_Engine;

		echo $mustache->render(file_get_contents(RESOURCES_ROOT . "/page.html"), $page->getData());
	}
}
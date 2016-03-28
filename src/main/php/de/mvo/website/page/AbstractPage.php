<?php
namespace de\mvo\website\page;

abstract class AbstractPage implements Page
{
	/**
	 * @var PageData
	 */
	private $data;

	public function __construct()
	{
		$this->data = new PageData;

		ob_start();
	}

	public function endContent()
	{
		$this->data->content = ob_get_clean();
	}

	protected function setTitle($title)
	{
		$this->data->title = $title;
	}

	public function getData()
	{
		return $this->data;
	}

	public function init()
	{
	}

	public function get()
	{
	}

	public function post()
	{
	}
}
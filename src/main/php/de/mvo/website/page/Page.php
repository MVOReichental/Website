<?php
namespace de\mvo\website\page;

interface Page
{
	public function init();
	public function get();
	public function post();
	public function endContent();
	public function getData();
}
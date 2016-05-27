<?php
namespace de\mvo\renderer;

interface Renderer
{
	public function setParams($params);
	public function render();
}
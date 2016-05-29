<?php
namespace de\mvo\router;

use de\mvo\router\exception\TargetConfigurationException;
use de\mvo\service\AbstractService;
use ReflectionClass;
use ReflectionMethod;

class Target
{
	public $class;
	public $method;
	public $params;
	public $methodArguments;

	public function __construct($class, $method, $methodArguments = array())
	{
		$this->class = $class;
		$this->method = $method;
		$this->methodArguments = $methodArguments;
	}

	public function call()
	{
		$reflectionClass = new ReflectionClass($this->class);

		if (!$reflectionClass->isInstantiable())
		{
			throw new TargetConfigurationException("Class '" . $this->class . "' is not instantiable");
		}

		if (!$reflectionClass->isSubclassOf(AbstractService::class))
		{
			throw new TargetConfigurationException("Class '" . $this->class . "' must be a subclass of '" . AbstractService::class . "'");
		}

		if (!$reflectionClass->hasMethod($this->method))
		{
			throw new TargetConfigurationException("Method '" . $this->method . "' not found in class '" . $this->class . "'");
		}

		/**
		 * @var $instance AbstractService
		 */
		$instance = $reflectionClass->newInstance();

		$reflectionMethod = new ReflectionMethod($instance, $this->method);

		if (!$reflectionMethod->isPublic())
		{
			throw new TargetConfigurationException("Method '" . $this->method . "' in class '" . $this->class . "' is not public");
		}

		$instance->params = $this->params;

		return $reflectionMethod->invokeArgs($instance, $this->methodArguments);
	}
}
<?php
namespace JulianSeymour\PHPWebApplicationFramework\session;

use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;

trait BindSessionColumnsTrait
{

	use MultipleColumnDefiningTrait;

	public function setBindIpAddress($bind)
	{
		return $this->setColumnValue("bindIpAddress", $bind);
	}

	public function setBindUserAgent($bind)
	{
		return $this->setColumnValue("bindUserAgent", $bind);
	}

	public function getBindUserAgent()
	{
		return $this->getColumnValue("bindUserAgent");
	}

	public function getBindIpAddress()
	{
		return $this->getColumnValue("bindIpAddress");
	}
}

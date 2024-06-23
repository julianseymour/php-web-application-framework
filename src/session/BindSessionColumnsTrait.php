<?php

namespace JulianSeymour\PHPWebApplicationFramework\session;

trait BindSessionColumnsTrait{
	
	public function setBindIpAddress($bind){
		return $this->setColumnValue("bindIpAddress", $bind);
	}

	public function setBindUserAgent($bind){
		return $this->setColumnValue("bindUserAgent", $bind);
	}

	public function getBindUserAgent(){
		return $this->getColumnValue("bindUserAgent");
	}

	public function getBindIpAddress(){
		return $this->getColumnValue("bindIpAddress");
	}
}

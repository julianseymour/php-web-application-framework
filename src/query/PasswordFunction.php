<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordTrait;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;

class PasswordFunction extends ExpressionCommand{

	use ColumnNameTrait;
	use PasswordTrait;

	public function __construct($vn = null, $password = null){
		parent::__construct();
		if(!empty($vn)){
			$this->setColumnName($vn);
		}
		if(!empty($password)){
			$this->setPassword($password);
		}
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasColumnName()){
			$this->setColumnName(replicate($that->getColumnName()));
		}
		if($that->hasPassword()){
			$this->setPassword(replicate($that->getPassword()));
		}
		return $ret;
	}

	public function getParameterCount():int{
		return 0;
	}

	public static function getCommandId(): string{
		return "password";
	}

	public function toSQL(): string{
		return "PASSWORD(" . single_quote($this->getPassword()) . ")";
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->columnName, $deallocate);
		$this->release($this->password, $deallocate);
	}
}

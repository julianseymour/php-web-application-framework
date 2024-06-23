<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\grant;

use function JulianSeymour\PHPWebApplicationFramework\implode_back_quotes;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\query\DatabaseVersionTrait;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnNamesTrait;
use Exception;

class DatabasePrivilege extends Basic implements SQLInterface{

	use MultipleColumnNamesTrait;
	use NamedTrait;
	use DatabaseVersionTrait;

	public function __construct($name = null, ...$columnNames){
		parent::__construct();
		$this->requirePropertyType("columnNames", 's');
		if(isset($name)){
			$this->setName($name);
		}
		if(isset($columnNames)){
			$this->setColumnNames($columnNames);
		}
	}

	public function dispose(bool $deallocate=false):void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		if($this->hasName()){
			$this->release($this->name, $deallocate);
		}
		if($this->hasPropertyTypes()){
			$this->release($this->propertyTypes, $deallocate);
		}
		if($this->hasRequiredMySQLVersion()){
			$this->release($this->requiredMySQLVersion, $deallocate);
		}
	}
	
	public function toSQL(): string{
		$f = __METHOD__;
		try{
			// priv_type [(column_list)] [, priv_type [(column_list)]] ...
			$string = $this->getName();
			if($this->hasColumnNames()){
				$string .= " (" . implode_back_quotes(',', $this->getColumnNames()) . ")";
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}

<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

abstract class AbstractDateTimeStringCommand extends StringTransformationCommand{
	
	protected $timezone;
	
	protected $format;
	
	public function __construct($subject = null, $timezone = null, ?string $format = null){
		parent::__construct($subject);
		if($timezone !== null){
			$this->setTimezone($timezone);
		}
		if($format !== null){
			$this->setFormat($format);
		}
	}
	
	public function hasTimezone(): bool{
		return isset($this->timezone);
	}
	
	public function setTimezone($timezone){
		if($this->hasTimezone()){
			$this->release($this->timezone);
		}
		return $this->timezone = $this->claim($timezone);
	}
	
	public function getTimezone(){
		if($this->hasTimezone()){
			return $this->timezone;
		}
		return null;
	}
	
	public function hasFormat(): bool{
		return isset($this->format);
	}
	
	public function setFormat($format){
		if($this->hasFormat()){
			$this->release($this->format);
		}
		return $this->format = $this->claim($format);
	}
	
	public function getFormat(){
		$f = __METHOD__;
		if(!$this->hasFormat()){
			Debug::error("{$f} format is undefined");
		}
		return $this->format;
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasFormat()){
			$this->setFormat(replicate($that->getFormat()));
		}
		if($that->hasTimezone()){
			$this->setTimezone(replicate($that->getTimezone()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->format, $deallocate);
		$this->release($this->timezone, $deallocate);
	}
}
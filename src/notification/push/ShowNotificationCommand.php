<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification\push;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\OptionsTrait;

class ShowNotificationCommand extends Command{

	use OptionsTrait;
	
	protected $title;
	
	public function __construct($title = null, $options = null){
		parent::__construct();
		if(!empty($title)){
			$this->setTitle($title);
		}
		if(!empty($options)){
			$this->setOptions($options);
		}
	}
	
	public function hasTitle():bool{
		return !empty($this->title);
	}

	public function getTitle(){
		$f = __METHOD__;
		if(!$this->hasTitle()){
			Debug::error("{$f} title is undefined");
		}
		return $this->title;
	}

	public function setTitle($title){
		if($this->hasTitle)
		return $this->title = $title;
	}

	public function echoInnerJson(bool $destroy = false): void{
		Json::echoKeyValuePair('title', $this->getTitle(), $destroy);
		if($this->hasOptions()){
			Json::echoKeyValuePair('options', $this->getOptions(), $destroy);
		}
		parent::echoInnerJson($destroy);
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->options, $deallocate);
		$this->release($this->title, $deallocate);
	}

	public static function getCommandId(): string{
		return "showNotification";
	}
}

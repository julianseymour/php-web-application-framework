<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui\infobox;

use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class InfoBoxResponder extends Responder{
	
	protected $info;
	
	public function __construct($info=null){
		parent::__construct();
		if($info !== null){
			$this->setInfo($info);
		}
	}
	
	public function hasInfo():bool{
		return isset($this->info) && !empty($this->info);
	}
	
	public function setInfo($info){
		if($info === null){
			unset($this->info);
			return null;
		}
		return $this->info = $info;
	}
	
	public function getInfo(){
		$f = __METHOD__;
		if(!$this->hasInfo()){
			Debug::error("{$f} info is undefined");
		}
		return $this->info;
	}
	
	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case):void{
		parent::modifyResponse($response, $use_case);
		$response->pushCommand(new InfoBoxCommand($this->getInfo()));
	}
}


<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;

use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\cache\CachePageContentCommand;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheResponder;
use JulianSeymour\PHPWebApplicationFramework\command\element\InsertAfterCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class InsertAfterResponder extends CacheResponder{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case){
		$f = __METHOD__;
		$print = false;
		parent::modifyResponse($response, $use_case);
		$inserted_object = $use_case->getDataOperandObject();
		$after_me = $use_case->getInsertHereElement($inserted_object);
		$object_element = $use_case->getElementForInsertion($inserted_object);
		if($after_me instanceof Element && !$after_me->hasIdAttribute()){
			Debug::error("{$f} ".$after_me->getDebugString()." lacks an ID attribute");
		}elseif($print){
			Debug::print("{$f} ".$after_me->getDebugString()." has ID attribute ".$after_me->getIdAttribute());
		}
		$insert = new InsertAfterCommand($after_me, $object_element);
		if($this->getCacheFlag()){
			$insert->pushSubcommand(new CachePageContentCommand());
		}
		$response->pushCommand($insert);
	}
}

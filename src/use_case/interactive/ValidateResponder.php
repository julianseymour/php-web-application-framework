<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;


use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\cache\CachePageContentCommand;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheResponder;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class ValidateResponder extends CacheResponder
{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case)
	{
		$f = __METHOD__;
		try{
			$print = false;
			parent::modifyResponse($response, $use_case);
			$data = $use_case->getDataOperandObject();
			$type = $data->getDataType();
			$ciec = $use_case->getConditionalElementClass($type);
			$element = new $ciec(ALLOCATION_MODE_LAZY);
			if($element instanceof AjaxForm){
				$element->setValidator($use_case->getValidator());
			}
			$element->bindContext($data);
			$update = $element->update();
			if($this->getCacheFlag()){
				$update->pushSubcommand(new CachePageContentCommand());
			}
			$response->pushCommand($update);
		}catch(Exception $x){
			x($f, $x);
		}
	}
}

<?php
namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;

use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\cache\CachePageContentCommand;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheResponder;
use JulianSeymour\PHPWebApplicationFramework\command\element\FadeElementCommand;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class DeleteResponder extends CacheResponder
{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case)
	{
		parent::modifyResponse($response, $use_case);
		$deleted_object = $use_case->getDataOperandObject();
		$datatype = $deleted_object->getDataType();
		$ciec = $use_case->getConditionalElementClass($datatype);
		$deleted_element = new $ciec(ALLOCATION_MODE_LAZY);
		$deleted_element->bindContext($deleted_object);
		$deleted_element->setDeletePredecessorsFlag(true);
		$deleted_element->setDeleteSuccessorsFlag(true);
		$fade = new FadeElementCommand($deleted_element);
		if($this->getCacheFlag()){
			$fade->pushSubcommand(new CachePageContentCommand());
		}
		$response->pushCommand($fade);
	}
}

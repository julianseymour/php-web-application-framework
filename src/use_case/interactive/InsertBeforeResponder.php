<?php
namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;

use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\cache\CachePageContentCommand;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheResponder;
use JulianSeymour\PHPWebApplicationFramework\command\element\InsertBeforeCommand;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class InsertBeforeResponder extends CacheResponder
{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case)
	{
		parent::modifyResponse($response, $use_case);
		$inserted_object = $use_case->getDataOperandObject();
		$before_me = $use_case->getInsertHereElement($inserted_object);
		$object_element = $use_case->getElementForInsertion($inserted_object);
		$insert = new InsertBeforeCommand($before_me, $object_element);
		if($this->getCacheFlag()) {
			$insert->pushSubcommand(new CachePageContentCommand());
		}
		$response->pushCommand($insert);
	}
}

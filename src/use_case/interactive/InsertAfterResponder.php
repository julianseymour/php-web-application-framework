<?php
namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;

use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\cache\CachePageContentCommand;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheResponder;
use JulianSeymour\PHPWebApplicationFramework\command\element\InsertAfterCommand;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class InsertAfterResponder extends CacheResponder
{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case)
	{
		parent::modifyResponse($response, $use_case);
		$inserted_object = $use_case->getDataOperandObject();
		$after_me = $use_case->getInsertHereElement($inserted_object);
		$object_element = $use_case->getElementForInsertion($inserted_object);
		$insert = new InsertAfterCommand($after_me, $object_element);
		if ($this->getCacheFlag()) {
			$insert->pushSubcommand(new CachePageContentCommand());
		}
		$response->pushCommand($insert);
	}
}

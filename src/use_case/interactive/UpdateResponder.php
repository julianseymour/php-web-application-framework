<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;


use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\cache\CachePageContentCommand;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheResponder;
use JulianSeymour\PHPWebApplicationFramework\command\element\UpdateElementCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class UpdateResponder extends CacheResponder
{

	public static function generateCommand(UseCase $use_case): UpdateElementCommand
	{
		$f = __METHOD__;
		try{
			$print = false;
			$data = $use_case->getDataOperandObject();
			$type = $data->getDataType();
			$ciec = $use_case->getConditionalElementClass($type);
			$old_element = new $ciec(ALLOCATION_MODE_LAZY);
			if($use_case->hasOriginalOperand()) {
				$backup = $use_case->getOriginalOperand();
				$old_element->bindContext($backup);
			}else{
				$backup = null;
			}
			$updated_element = $use_case->getElementForUpdate($backup);
			$old_id = $old_element->getIdAttribute();
			if(! isset($old_id)) {
				Debug::error("{$f} old ID is undefined");
			}elseif($print) {
				Debug::print("{$f} old element ID is \"{$old_id}\"");
			}
			$update = $updated_element->update();
			return $update;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case)
	{
		$f = __METHOD__;
		try{
			parent::modifyResponse($response, $use_case);
			$update = static::generateCommand($use_case);
			if($this->getCacheFlag()) {
				$update->pushSubcommand(new CachePageContentCommand());
			}
			$response->pushCommand(static::generateCommand($use_case));
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}

<?php

namespace JulianSeymour\PHPWebApplicationFramework\language\settings;

use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\cache\CachePageContentCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\UpdateElementCommand;
use JulianSeymour\PHPWebApplicationFramework\ui\RiggedBodyElement;
use JulianSeymour\PHPWebApplicationFramework\ui\RiggedHeadElement;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class SetLanguageResponder extends Responder{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case):void{
		parent::modifyResponse($response, $use_case);
		$set_src = RiggedHeadElement::getScriptBundleElement()->update();
		$set_src->setOptional(true);
		$set_src->setEffect(EFFECT_NONE);
		$response->pushCommand(
			$set_src, 
			new UpdateElementCommand(
				new RiggedBodyElement(
					ALLOCATION_MODE_LAZY, 
					$use_case->getPageContentGenerator()
				)
			), 
			new CachePageContentCommand()
		);
	}
}

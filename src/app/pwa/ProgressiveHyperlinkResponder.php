<?php
namespace JulianSeymour\PHPWebApplicationFramework\app\pwa;

use function JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\form\SetUniversalFormActionCommand;
use JulianSeymour\PHPWebApplicationFramework\ui\PageContentElement;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\form\InitializeAllFormsCommand;

class ProgressiveHyperlinkResponder extends Responder{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case){
		$f = __METHOD__;
		try{
			$print = false;
			$uri = request()->getRequestURI();
			if($print){
				Debug::print("{$f} pushing URI \"{$uri}\"");
			}
			if(!$response->getOverrideHyperlinkBehaviorFlag()){
				if($print){
					Debug::print("{$f} not overriding hyperlink behavior");
					// Debug::printStackTraceNoExit("{$f}");
				}
				if(ULTRA_LAZY){
					$mode = ALLOCATION_MODE_ULTRA_LAZY;
				}else{
					$mode = ALLOCATION_MODE_LAZY;
				}
				if($print){
					Debug::print("{$f} this is a progressive hyperlink event");
				}
				// page content
				$page_content = new PageContentElement($mode);
				$status = $use_case->permit(user(), "execute");
				if($status === SUCCESS){
					$contents = $use_case->getPageContent();
				}else{
					$contents = [
						ErrorMessage::getVisualError($status)
					];
				}
				$page_content->saveChild(...$contents);
				$update_pg_content = $page_content->updateInnerHTML();
				$update_pg_content->pushSubcommand(
					new InitializeAllFormsCommand(),
					$page_content->scrollIntoView(true)
					
				);
				$response->pushCommand($update_pg_content);
				$response->setFlag('pwa');
			}elseif($print){
				Debug::print("{$f} hyperlink behavior overridden");
			}
			if(APPLICATION_INTEGRATION_MODE === APP_INTEGRATION_MODE_UNIVERSAL){
				$response->pushCommand(new SetUniversalFormActionCommand($_SERVER['REQUEST_URI']));
			}
			parent::modifyResponse($response, $use_case);
		}catch(Exception $x){
			x($f, $x);
		}
	}
}

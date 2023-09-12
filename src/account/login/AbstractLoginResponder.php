<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\login;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\input\SetInputValueCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\ui\PageContentElement;
use JulianSeymour\PHPWebApplicationFramework\ui\WidgetContainer;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

abstract class AbstractLoginResponder extends Responder{

	protected function getStartingResponseCommandArray(): array{
		$notify_ts = user()->getNotificationDeliveryTimestamp();
		$ts_command = new SetInputValueCommand("notify_ts", $notify_ts);
		return [
			$ts_command
		];
	}

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case){
		$f = __METHOD__;
		try{
			$print = false;
			parent::modifyResponse($response, $use_case);
			$user = user();
			if($user == null) {
				Debug::error("{$f} user returned null");
				$status = ERROR_NULL_USER_OBJECT;
				return $status;
			}
			if(ULTRA_LAZY) {
				$mode = ALLOCATION_MODE_ULTRA_LAZY;
			}else{
				$mode = ALLOCATION_MODE_LAZY;
			}
			$commands = $this->getStartingResponseCommandArray();
			// widgets
			$widgets = mods()->getWidgetClasses($use_case);
			if(!empty($widgets)) {
				$i = 0;
				foreach($widgets as $widget_class) {
					$container = new WidgetContainer($mode);
					$container->setIterator($i ++);
					$container->setWidgetClass($widget_class);
					$container->bindContext($user);
					$command = $container->update();
					if($print) {
						Debug::print("{$f} pushing login response command for widget class \"{$widget_class}\"");
					}
					array_push($commands, $command);
				}
			}
			if(!$use_case instanceof ExecutiveLoginUseCase){
				// page content
				if($use_case->isPageUpdatedAfterLogin()) {
					if($print) {
						Debug::print("{$f} use case does update the page after login");
					}
					$div = new PageContentElement($mode);
					$content = $use_case->getPageContent();
					$div->saveChild(...$content);
					$div->setCatchReportedSubcommandsFlag(true);
					$page_command = $div->updateInnerHTML();
					array_push($commands, $page_command);
				}else{
					if($print) {
						Debug::print("{$f} no, the use case does not update the page after login");
					}
				}
			}elseif($print){
				Debug::print("{$f} use case is an ExecutiveLoginUseCase");
			}
			// put them all together
			foreach($commands as $command) {
				if(is_array($command)) {
					Debug::error("{$f} string \"{$command}\"");
				}
			}
			$linked_command = Command::linkCommands(...$commands);
			if(is_array($linked_command)) {
				Debug::error("{$f} linked commands is an array");
			}
			// notification delivery timestamp
			$notify_ts = CommandBuilder::setValue(CommandBuilder::getElementById("notify_ts"), $user->getNotificationDeliveryTimestamp());
			$response->pushCommand($linked_command, $notify_ts);
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}


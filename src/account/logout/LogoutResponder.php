<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\logout;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\element\UpdateElementCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\ui\FlipPanels;
use JulianSeymour\PHPWebApplicationFramework\ui\PageContentElement;
use JulianSeymour\PHPWebApplicationFramework\ui\WidgetContainer;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class LogoutResponder extends Responder{

	public static function pushWidgetCommands(XMLHttpResponse $response, UseCase $use_case){
		$f = __METHOD__;
		$print = false;
		$mode = ALLOCATION_MODE_LAZY;
		if (APPLICATION_INTEGRATION_MODE === APP_INTEGRATION_MODE_UNIVERSAL) {
			$cp = DivElement::wrap(new FlipPanels($mode, user()));
			$cp->setIdAttribute("login_replace");
			$cp->addClassAttribute("login_replace", "login_form_container");
			$update = new UpdateElementCommand($cp);
			$response->pushCommand($update);
		}
		$widgets = mods()->getWidgetClasses($use_case);
		if (! empty($widgets)) {
			$count = 0;
			$user = user();
			foreach ($widgets as $widget_class) {
				$container = new WidgetContainer($mode);
				$container->setIterator($count++);
				$container->setWidgetClass($widget_class);
				$container->bindContext($user);
				$container->setStyleProperties([
					'opacity' => '0'
				]);
				$update = $container->update()->optional();
				$update->setEffect(EFFECT_NONE);
				$wid = $widget_class::getWidgetLabelId();
				$update->pushSubcommand(
					CommandBuilder::if(
						CommandBuilder::call("elementExists", single_quote($wid))
					)->then(
						CommandBuilder::call("enable", single_quote($wid))
					)->else(
						CommandBuilder::log("Widget label \"{$wid}\" does not exist, skipping enable")
					)
				);
				$response->pushCommand($update);
			}
		}
	}

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case){
		$f = __METHOD__;
		try {
			$print = false;
			parent::modifyResponse($response, $use_case);
			static::pushWidgetCommands($response, $use_case);
			$status = $use_case->permit(user(), "execute");
			if ($status === SUCCESS) {
				$use_case->getPageContentGenerator()->setObjectStatus(RESULT_LOGGED_OUT);
				$contents = $use_case->getPageContent();
			} else {
				$contents = [
					ErrorMessage::getVisualError($status)
				];
			}
			$page = PageContentElement::wrap(...$contents);
			if (! $page->hasChildNodes()) {
				$ucc = $use_case->getClass();
				Debug::error("{$f} use case failed to generate child nodes for page content. Use case class is \"{$ucc}\"");
			}
			$pg_update = $page->updateInnerHTML();
			$response->pushCommand($pg_update);
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}


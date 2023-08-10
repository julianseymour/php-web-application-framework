<?php
namespace JulianSeymour\PHPWebApplicationFramework\language\settings;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\cache\CachePageContentCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\UpdateElementCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\ui\FooterContent;
use JulianSeymour\PHPWebApplicationFramework\ui\PageContentElement;
use JulianSeymour\PHPWebApplicationFramework\ui\RiggedHeadElement;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class SetLanguageResponder extends Responder
{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case)
	{
		$f = __METHOD__;
		$print = false;
		parent::modifyResponse($response, $use_case);
		$predecessor = $use_case->getPredecessor();
		$context = user();
		$mode = ALLOCATION_MODE_LAZY;
		$menu_class = config()->getMenuElementClass();
		$menu = new $menu_class($mode, $context);

		$script = RiggedHeadElement::getStringTableScriptElement();
		$set_src = new UpdateElementCommand($script);
		$set_src->setOptional(true);
		$set_src->setEffect(EFFECT_NONE);

		$response->pushCommand($set_src, new UpdateElementCommand($menu));
		$footer = new FooterContent($mode, $context);
		$response->pushCommand($footer->update);
		if ($predecessor->isPageUpdatedAfterLogin()) {
			if ($print) {
				Debug::print("{$f} use case updates the page after login");
			}
			$arr = $predecessor->getPageContent();
			$page_content = new PageContentElement($mode);
			$page_content->appendChild(...$arr);
			// $page_content->setIdAttribute("page_content");
			$response->pushCommand($page_content->updateInnerHTML()); // new UpdateElementCommand($page_content));
		}
		$response->pushSubcommand(new CachePageContentCommand());
		return SUCCESS;
	}
}

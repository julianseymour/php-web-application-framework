<?php

namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\getlocale;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\use_case;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\element\BodyElement;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\poll\ShortPollForm;
use JulianSeymour\PHPWebApplicationFramework\security\xsrf\AntiXsrfTokenContainer;
use JulianSeymour\PHPWebApplicationFramework\session\timeout\SessionTimeoutOverlay;
use JulianSeymour\PHPWebApplicationFramework\ui\infobox\InfoBoxElement;
use Exception;

class RiggedBodyElement extends BodyElement{

	protected static function allowUseCaseAsContext(): bool{
		return true;
	}

	public function __construct(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setIdAttribute("body");
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try{
			$print = false;
			$context = $this->getContext();
			$use_case = app()->getUseCase();
			$user = app()->hasUserData() ? user() : null;
			$mode = $this->getAllocationMode();
			if($use_case->hasMenu()) {
				$banner_class = config()->getBannerElementClass();
				$banner = new $banner_class($mode, $user);
				$this->appendChild($banner);
			}
			$webkit_fix = new DivElement($mode);
			$webkit_fix->addClassAttribute("webkit-overflow-scroll-fix");
			$webkit_fix->setIdAttribute("webkit-overflow-scroll-fix");
			$content_and_footer = new DivElement($mode);
			$content_and_footer->addClassAttribute("content_and_footer");
			$page_content = new PageContentElement($mode);
			$status = $use_case->getObjectStatus(); //XXX leave this alone for now
			$content_array = $use_case->getPageContent();
			if(empty($content_array)) {
				$page_content->appendChild(ErrorMessage::getVisualError($use_case->getObjectStatus()));
			}elseif(!is_array($content_array)) {
				$ccn = $use_case->getClass();
				Debug::error("{$f} {$ccn}->getPageContent should return an array of elements");
			}else{
				$page_content->appendChild(...$content_array);
			}
			$content_and_footer->appendChild($page_content);
			if($use_case->hasMenu()) {
				$footer_class = config()->getFooterElementClass();
				if(is_a($footer_class, Element::class, true)){
					if($print){
						Debug::print("{$f} footer element class is \"{$footer_class}\"");
					}
					$footer = new $footer_class($mode, $user);
					$content_and_footer->appendChild($footer);
					if($print) {
						Debug::print("{$f} appended footer");
					}
				}elseif($print){
					Debug::print("{$f} no footer class");
				}
			}elseif($print) {
				if($user == null) {
					Debug::error("{$f} skipping footer because user is null");
				}else{
					Debug::print("{$f} skipping footer because this use case does not have a menu");
				}
			}
			$webkit_fix->appendChild($content_and_footer);
			$this->appendChild($webkit_fix);
			if($user !== null && $use_case->hasMenu()) {
				$menu_class = config()->getMenuElementClass();
				$menu = new $menu_class($mode, $user);
				$fixed = new DivElement($mode);
				$fixed->addClassAttribute("fixed");
				$fixed->setIdAttribute("fixed");
				$fixed->appendChild($menu);
				$widget_classes = mods()->getWidgetClasses(use_case());
				if($print) {
					if(empty($widget_classes)) {
						Debug::print("{$f} there are no widget classes");
					}else{
						$count = count($widget_classes);
						Debug::print("{$f} {$count} widget classes");
					}
				}
				$fixed->appendChild(new ToolBelt($mode, $user));
				if(false && class_exists(SessionTimeoutOverlay::class)) {
					$session_timeout = new SessionTimeoutOverlay($mode, $user);
					$session_timeout->setCacheKey(SessionTimeoutOverlay::class);
					$session_timeout->setHTMLCacheableFlag(false);
					$fixed->appendChild($session_timeout);
				}
				if(class_exists(InfoBoxElement::class)) {
					$info_box = new InfoBoxElement($mode, $user);
					$fixed->appendChild($info_box);
				}
				$xsrf = new AntiXsrfTokenContainer($mode, $user);
				$fixed->appendChild($xsrf);
				$this->appendChild($fixed);
			}elseif($print) {
				Debug::print("{$f} skipping menu");
			}
			if($user instanceof PlayableUser){
				$this->appendChild(new ShortPollForm($mode, $user));
			}
			if(! Request::isAjaxRequest()) {
				if($print) {
					Debug::print("{$f} this is not an AJAX request");
				}
				$this->appendChild(
					Document::createElement("script")->withIdAttribute("cache_pg_content_script")->withInnerHTML("initializePopState();cachePageContent();"), 
					Document::createElement("script")->withIdAttribute("initialize_forms_script")->withInnerHTML("AjaxForm.initializeAllForms();"), 
					Document::createElement("script")->withIdAttribute("window_resize_script")->withChild(
						CommandBuilder::addEventListener("window.visualViewport", "resize", "windowResizeListener")
					)
				);
			}elseif($print) {
				Debug::print("{$f} this is an AJAX request");
			}
			$condition = "isMobileSafari()";
			$this->appendChild(
				Document::createElement("script")->withInnerHTML(
					"resetSessionTimeoutAnimation(true);\n" . 
					CommandBuilder::if($condition)->then(
						CommandBuilder::call("Menu.setViewportHeightCustomProperty")
					)->else(
						CommandBuilder::log("You are not browsing with mobile safari")
					)->toJavaScript()
				)
			);
			if($print) {
				Debug::print("{$f} generated child nodes");
			}
			return $this->getChildNodes();
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}

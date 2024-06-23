<?php
namespace JulianSeymour\PHPWebApplicationFramework\session\timeout;


use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\pwa\ProgressiveHyperlinkElement;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\image\ThemeSensitiveImageElement;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class SessionTimeoutOverlay extends DivElement
{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		$f = __METHOD__;
		if($context instanceof UseCase){
			Debug::error("{$f} context is a use case");
		}
		parent::__construct($mode, $context);
		$this->addClassAttribute("session_timeout_overlay");
		$this->setIdAttribute("session_timeout_overlay");
	}

	public function generateChildNodes(): ?array
	{
		$f = __METHOD__;
		try{
			$context = $this->getContext();
			$mode = $this->getAllocationMode();
			$session_timeout_1 = new DivElement($mode);
			$session_timeout_1->setIdAttribute("session_timeout_1");
			$form = new RefreshSessionForm($mode, $context);
			$session_timeout_1->appendChild($form);
			$this->appendChild($session_timeout_1);
			$session_timeout_2 = new ProgressiveHyperlinkElement($mode);
			$session_timeout_2->setIdAttribute("session_timeout_2");
			$logo_c = new DivElement($mode);
			$logo_c->setStyleProperties([
				"width" => "100%",
				"padding-bottom" => "100%"
			]);
			$logo = new ThemeSensitiveImageElement($mode);
			$logo->setHrefAttribute(request()->getRequestURI());
			$logo->setAlternateTextAttribute(DOMAIN_LOWERCASE);
			$logo->setDarkThemeImageURI(WEBSITE_LOGO_URI_VERTICAL_DARK);
			$logo->setLightThemeImageURI(WEBSITE_LOGO_URI_VERTICAL_LIGHT);
			$logo_c->appendChild($logo);
			$expired = new DivElement();
			$expired->addClassAttribute("background_color_1");
			$expired->setInnerHTML(ErrorMessage::getResultMessage(ERROR_SESSION_EXPIRED));
			$session_timeout_2->appendChild($logo_c, $expired);
			$session_timeout_2->setHrefAttribute(request()->getRequestURI());
			$this->appendChild($session_timeout_2);
			return $this->hasChildNodes() ? $this->getChildNodes() : [];
		}catch(Exception $x){
			x($f, $x);
		}
	}
}

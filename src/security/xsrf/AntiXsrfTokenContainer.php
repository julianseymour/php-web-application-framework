<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\xsrf;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use Exception;

/**
 * This element contains HMAC tokens for forms that get rendered client side
 *
 * @author j
 *        
 */
class AntiXsrfTokenContainer extends DivElement{

	public function __construct(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setIdAttribute("xsrf_c");
		$this->addClassAttribute("hidden");
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try{
			$session = new AntiXsrfTokenData();
			if(!$session->hasAntiXsrfToken()){
				Debug::error("{$f} session is uninitialized");
				$session->initializeSessionToken(1);
			}
			$input = new HiddenInput();
			$input->setValueAttribute($session->getAntiXsrfToken());
			$input->setIdAttribute("xsrf_token");
			$this->appendChild($input);
			$forms = mods()->getClientRenderedFormClasses();
			foreach($forms as $form){
				$action = $form::getActionAttributeStatic();
				if($action === null || strlen($action) === 0){
					continue;
				}
				$input = new HiddenInput();
				$secondary_hmac = $session->getSecondaryHmac($action);
				$input->setValueAttribute($secondary_hmac);
				$suffix = strtolower(str_replace('/', '', $action));
				$input->setIdAttribute("secondary_hmac-{$suffix}");
				$this->appendChild($input);
			}
			deallocate($session);
			return $this->hasChildNodes() ? $this->getChildNodes() : [];
		}catch(Exception $x){
			x($f, $x);
		}
	}
}

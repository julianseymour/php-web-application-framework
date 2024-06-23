<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\nonexistent_uris;

use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\GhostButton;

class NonexistentUriForm extends AjaxForm{
	
	public function bindContext($context){
		$ret = parent::bindContext($context);
		$key = $this->getResolvedKey($context);
		$id = new ConcatenateCommand("nonexistent_uri_form-", $key);
		$this->setIdAttribute($id);
		return $ret;
	}
	
	public static function getMethodAttributeStatic():?string{
		return HTTP_REQUEST_METHOD_POST;
	}
	
	public function getDirectives(): ?array{
		return [
			DIRECTIVE_UPDATE, 
			DIRECTIVE_DELETE
		];
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "nonexistent_uris";
	}
	
	public static function getActionAttributeStatic(): ?string{
		return '/nonexistent_uris';
	}
	
	public function generateFormHeader():void{
		$context = $this->getContext();
		$ip = $context->getColumnValue("ipAddress");
		$uri = $context->getColumnValue("requestUri");
		$this->appendChild(Document::createElement("div")->withInnerHTML("{$ip} attempted to access {$uri}"));
	}
	
	public function getFormDataIndices(): ?array{
		return [
			"list" => GhostButton::class
		];
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		$context = $this->getContext();
		$list = $context->getColumnValue("list");
		switch($name){
			case DIRECTIVE_UPDATE:
				$ban = $this->generateGenericButton($name, POLICY_BLOCK);
				$ban->setNameAttribute("directive[update][list]");
				$ban->setInnerHTML(_("Ban"));
				$authorize = $this->generateGenericButton($name, POLICY_ALLOW);
				$authorize->setNameAttribute("directive[update][list]");
				$authorize->setInnerHTML(_("Authorize"));
				switch($list){
					case POLICY_ALLOW:
						return [
							$ban
						];
					case POLICY_BLOCK:
						return [
							$authorize
						];
					default:
						return [
							$authorize,
							$ban
						];
				}
			case DIRECTIVE_DELETE:
				return [
					$this->generateGenericButton($name)
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}
}


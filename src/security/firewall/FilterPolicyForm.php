<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\GhostButton;
use JulianSeymour\PHPWebApplicationFramework\input\ToggleInput;
use Exception;

class FilterPolicyForm extends AjaxForm{

	public function generateFormHeader(): void{
		$div = new DivElement();
		$div->appendChild(_("With block policy enabled, unauthorized IP addresses will be unable to access your account until authorized via a link sent to your email address. This email can be disabled (this is not recommended)."));
		$this->appendChild($div);
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_UPDATE
		];
	}

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try{
			$vn = $input->getColumnName();
			switch($vn){
				case "filterPolicy":
					$input->setIdAttribute("whitelist_mode");
					break;
				case "authLinkEnabled":
					$input->setIdAttribute("send_auth_email");
					break;
				default:
			}
			return parent::reconfigureInput($input);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("filter_policy_element");
		$this->addClassAttribute("ip_list");
		$this->addClassAttribute("background_color_2");
		$this->setIdAttribute("filter_policy_form");
	}

	public function getFormDataIndices(): ?array{
		return [
			'authLinkEnabled' => ToggleInput::class,
			'filterPolicy' => GhostButton::class
		];
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "filter_policy";
	}

	public static function getActionAttributeStatic(): ?string{
		return '/account_firewall';
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch($name){
			case DIRECTIVE_UPDATE:
				$button = $this->generateGenericButton($name);
				$innerHTML = _("Update firewall settings");
				$button->setInnerHTML($innerHTML);
				$context = $this->getContext();
				if($context->getFilterPolicy() === POLICY_BLOCK){
					$value = POLICY_ALLOW;
					$policyname = _("Allow");
				}else{
					$value = POLICY_BLOCK;
					$policyname = _("Block");
				}
				$button2 = $this->generateGenericButton($name, $value);
				$button2->setNameAttribute("directive[update][filterPolicy]");
				$button2->setValueAttribute($value);
				$button2->setInnerHTML(substitute(_("Set security policy to '%1%'"), $policyname));
				return [
					$button,
					$button2
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}
}

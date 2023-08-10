<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ConfirmationCodeForm;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\input\ButtonInput;

class ConfirmIpAddressListForm extends ConfirmationCodeForm
{

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_VALIDATE
		];
	}

	public function __construct($mode = ALLOCATION_MODE_LAZY, $context = null){
		parent::__construct($mode, $context);
		$this->setIdAttribute("confirm_ip_list_form");
	}

	public function generateFormHeader(): void{
		$div = new DivElement();
		$context = $this->getContext();
		$innerHTML = substitute(_("Firewall settings for IP address %1%"), $context->getIpAddress());
		$div->setInnerHTML($innerHTML);
		$this->appendChild($div);
	}

	public function getFormDataIndices(): ?array{
		return [
			'list' => ButtonInput::class
		];
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "authorize_ban";
	}

	public static function getActionAttributeStatic(): ?string{
		return '/authorize_ip';
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch ($name) {
			case DIRECTIVE_VALIDATE:
				$authorize = $this->generateGenericButton($name);
				$authorize->setInnerHTML(_("Authorize"));
				$authorize->setValueAttribute('authorize');
				$ban = $this->generateGenericButton($name);
				$ban->setInnerHTML(_("Ban"));
				$ban->setValueAttribute('ban');
				return [
					$authorize,
					$ban
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}
}

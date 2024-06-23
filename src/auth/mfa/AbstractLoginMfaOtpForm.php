<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa;

use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\input\FocusInputCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\NumberInput;
use Exception;

abstract class AbstractLoginMfaOtpForm extends AjaxForm{

	public function generateFormHeader(): void{
		$div = new DivElement();
		$div->setInnerHTML(_("Enter MFA OTP from your authenticator app."));
		$this->appendChild($div);
	}

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try{
			$vn = $input->getColumnName();
			switch($vn){
				case "otp":
					$id = "mfa_otp";
					$input->setIdAttribute($id);
					$input->addClassAttribute("hide_num_arrows");
					break;
				default:
			}
			return parent::reconfigureInput($input);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_MFA
		];
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("mfa_form");
		$this->setIdAttribute("mfa_form");
	}

	public function getFormDataIndices(): ?array{
		return [
			"otp" => NumberInput::class
		];
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "login_mfa";
	}

	public static function getActionAttributeStatic(): ?string{
		return request()->getRequestURI();
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch($name){
			case DIRECTIVE_MFA:
				$button = $this->generateGenericButton(DIRECTIVE_MFA);
				$button->setInnerHTML(_("Submit code"));
				// $button->setNameAttribute(DIRECTIVE_MFA);
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}

	public function dispatchCommands(): int{
		$f = __METHOD__;
		try{
			$ret = parent::dispatchCommands();
			$command = new FocusInputCommand("mfa_otp");
			$this->reportSubcommand($command);
			return $ret;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}

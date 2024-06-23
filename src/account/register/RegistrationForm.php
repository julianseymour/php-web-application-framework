<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\register;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\get_class_filename;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\settings\timezone\GetUserTimezoneCommand;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordGeneratingFormInterface;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordGeneratingFormTrait;
use JulianSeymour\PHPWebApplicationFramework\command\element\GetElementByIdCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\SetInputValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\SoftDisableInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\SubstituteCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\ScriptElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\AnchorElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\CheckboxInput;
use JulianSeymour\PHPWebApplicationFramework\input\EmailInput;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use JulianSeymour\PHPWebApplicationFramework\input\InputlikeInterface;
use JulianSeymour\PHPWebApplicationFramework\input\PasswordInput;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\PHPWebApplicationFramework\security\captcha\hCaptcha;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;
use Exception;

class RegistrationForm extends AjaxForm implements PasswordGeneratingFormInterface{

	use JavaScriptCounterpartTrait;
	use PasswordGeneratingFormTrait;

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		$f = __METHOD__;
		parent::__construct($mode, $context);
		$this->setIdAttribute("register_form");
		$this->addClassAttribute('register_form');
	}

	public static function getJavaScriptClassPath(): ?string{
		$fn = get_class_filename(static::class);
		return substr($fn, 0, strlen($fn) - 3) . "js";
	}
	
	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public function getValidator(): ?Validator{
		if($this->hasValidator()){
			return $this->validator;
		}
		$use_case = app()->getUseCase();
		return $this->setValidator(new RegistrationValidator($use_case));
	}

	protected function attachInputValidators(InputlikeInterface $input): InputlikeInterface{
		$f = __METHOD__;
		$print = false;
		if(!$input->hasColumnName()){
			return $input;
		}
		$vn = $input->getColumnName();
		if($print){
			Debug::print("{$f} variable name is \"{$vn}\"");
		}
		switch($vn){
			case "emailAddress":
				$input->pushValidator(new RegistrationEmailAddressValidator());
				break;
			case "password":
				$input->pushValidator($this->getConfirmPasswordValidator());
				break;
			default:
				break;
		}
		return $input;
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try{
			$print = false;
			if(!$input->hasColumnName()){
				if($print){
					Debug::print("{$f} username does not have a column name");
				}
				return parent::reconfigureInput($input);
			}
			$vn = $input->getColumnName();
			switch($vn){
				case "emailAddress":
					$id = "reg_email";
					$input->setIdAttribute($id);
					$input->setAutocompleteAttribute("off");
					$ret = parent::reconfigureInput($input);
					$suffix = new DivElement();
					$suffix->addClassAttribute("js_valid_light");
					$suffix->setIdAttribute("js_valid_email");
					$suffix->setAllowEmptyInnerHTML(true);
					$input->pushSuccessor($suffix);
					$input->setRequiredAttribute("required");
					$input->setLabelString(_("Email address"));
					$input->getWrapperElement()->setStyleProperties([
						"margin-bottom" => "0.5rem"
					]);
					return $ret;
				case "name":
					return SUCCESS;
				case "password":
					if($print){
						Debug::printStackTraceNoExit("{$f} reconfiguring password input");
					}
					$id = "reg_password";
					$input->setIdAttribute($id);
					$input->setAutocompleteAttribute("off");
					$placeholder = _("Password") . " (" . substitute(_("%1%+ characters"), 12) . ")";
					$input->setLabelString($placeholder);
					$ret = parent::reconfigureInput($input);
					$suffix = new DivElement();
					$suffix->addClassAttribute("js_valid_light");
					$suffix->setIdAttribute("js_valid_password");
					$suffix->setAllowEmptyInnerHTML(true);
					$input->pushSuccessor($suffix);
					$input->setRequiredAttribute("required");
					$input->getWrapperElement()->setStyleProperties([
						//"height" => "calc(100px + 1rem)",
						"margin-bottom" => "0.5rem"
					]);
					$input->setStyleProperties([
						"margin-bottom" => "0.5rem"
					]);
					return $ret;
				case "timezone":
					$input->setIdAttribute("register_timezone");
					// XXX
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
			DIRECTIVE_INSERT
		];
	}

	public function dispatchCommands(): int{
		$register_timezone = new GetElementByIdCommand("register_timezone");
		$get_timezone = new GetUserTimezoneCommand();
		$command = new SetInputValueCommand(
			$register_timezone,
			$get_timezone
		);
		$this->reportSubcommand($command);
		return parent::dispatchCommands();
	}

	public function generateFormFooter():void{
		parent::generateFormFooter();
		if(!Request::isAjaxRequest()){
			$register_timezone = new GetElementByIdCommand("register_timezone");
			$get_timezone = new GetUserTimezoneCommand();
			$command = new SetInputValueCommand(
				$register_timezone,
				$get_timezone
			);
			$script = new ScriptElement();
			$script->appendChild($command);
			$this->appendChild($script);
		}
	}

	public function getAdHocInputs(): ?array{
		$f = __METHOD__;
		try{
			$inputs = parent::getAdHocInputs();
			$mode = $this->getAllocationMode();
			$context_input = new HiddenInput($mode);
			$context_input->setNameAttribute("context");
			$context_input->setValueAttribute("register");
			$ai = new CheckboxInput($mode);
			$ai->setNameAttribute("agree_tos");
			$ai->setIdAttribute("agree_tos");
			$ai->setValueAttribute(0);
			$agree_terms_label = new SpanElement($mode);
			$agree_terms_label->addClassAttribute("terms_box");
			$agree_terms_label->setIdAttribute("js_valid_terms");
			$a1 = new AnchorElement($mode);
			$a1->setHrefAttribute('/terms');
			$a1->setInnerHTML(_("Terms of service"));
			$sub = new SubstituteCommand(_("I agree to the %1%"), $a1);
			$agree_terms_label->setinnerHTML($sub);
			deallocate($sub);
			$ai->pushSuccessor($agree_terms_label);
			$agree_terms_wrapper = new DivElement($mode);
			$agree_terms_wrapper->addClassAttribute("text-align_center", "thumbsize");
			$agree_terms_wrapper->setStyleProperties([
				"display" => "block",
				"position" => "relative",
				"margin-bottom" => "0.5rem"
			]);
			$ai->setWrapperElement($agree_terms_wrapper);
			/*$onclick = "agreeTermsClickHandler(event, this);";
			$ai->setOnClickAttribute($onclick);*/
			foreach([
				$ai,
				$context_input
			] as $input){
				$inputs[$input->getNameAttribute()] = $input;
			}
			$hcaptcha = new hCaptcha($mode, $this->getContext());
			$hcaptcha->setIdAttribute('register_hcaptcha');
			$inputs['hCaptcha'] = $hcaptcha;
			return $inputs;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getFormDataIndices(): ?array{
		return [
			"userNameKey" => RegistrationUsernameForm::class,
			"emailAddress" => EmailInput::class,
			"password" => PasswordInput::class,
			"timezone" => HiddenInput::class
		];
	}

	public static function getHoneypotCountArray(): ?array{
		return [];
		// XXX problem -- in order for this system to work against a simple bot that works by getting the ID attribute, the IDs of each input must be randomized too -- but that will break client side interactivity e.g. content validation pre-registration
		return [
			"name" => 3,
			'emailAddress' => 3,
			"password" => 3
		];
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "register";
	}

	public static function getActionAttributeStatic(): ?string{
		return '/register';
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch($name){
			case DIRECTIVE_INSERT:
				$button = $this->generateGenericButton($name);
				// $button->setIdAttribute($this->getButtonIdAttribute());
				$innerHTML = _("Register");
				$button->setInnerHTML($innerHTML);
				// $button->setOnClickAttribute($this->get());
				if(Request::isXHREvent()){
					$subcommand = new SoftDisableInputCommand($button);
					$this->reportSubcommand($subcommand);
				}
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}

	public static function getPasswordInputName(): string{
		return "password";
	}

	public static function getConfirmPasswordInputName(): string{
		return "confirm";
	}
}

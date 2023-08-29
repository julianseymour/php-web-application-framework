<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\login;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\get_class_filename;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordDatum;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\email\EmailAddressDatum;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\form\UniqueFormInterface;
use JulianSeymour\PHPWebApplicationFramework\input\ButtonInput;
use JulianSeymour\PHPWebApplicationFramework\input\EmailInput;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use JulianSeymour\PHPWebApplicationFramework\input\InputElement;
use JulianSeymour\PHPWebApplicationFramework\input\PasswordInput;
use JulianSeymour\PHPWebApplicationFramework\input\RadioButtonInput;
use JulianSeymour\PHPWebApplicationFramework\input\TextInput;
use JulianSeymour\PHPWebApplicationFramework\security\captcha\LenienthCaptchaValidator;
use JulianSeymour\PHPWebApplicationFramework\security\captcha\hCaptcha;
use JulianSeymour\PHPWebApplicationFramework\security\honeypot\HoneypotValidator;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateElementInterface;
use JulianSeymour\PHPWebApplicationFramework\validate\FormButtonValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;
use Exception;

class LoginForm extends AjaxForm implements TemplateElementInterface, UniqueFormInterface{

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
		$validator = new FormButtonValidator($this);
		$honeypots = new HoneypotValidator(LoginForm::class);
		$captcha = new LenienthCaptchaValidator(LoginAttempt::class, 1);
		$validator->pushCovalidators($honeypots, $captcha);
		return $this->setValidator($validator);
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		$f = __METHOD__;
		try {
			parent::__construct($mode, $context);
			$this->setMethodAttribute("post");
			// $this->setIdAttribute("login_form");
			$this->addClassAttribute("login_form", "universal_form");
			$this->setStyleProperty("transition", "opacity 0.5s");
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function generateFormFooter(): void{
		$f = __METHOD__;
		try {
			$mode = $this->getAllocationMode();
			$back = _("Back to login");
			$forgot_password_hint = new LabelElement($mode);
			$forgot_password_hint->addClassAttribute("forgot_password_hint", "button-like");
			$forgot_password_hint->setForAttribute("radio_submit_login");
			$forgot_password_hint->setInnerHTML($back);
			$forgot_password_hint->setStyleProperties([
				'display' => 'none',
				'position' => 'relative'
			]);
			$this->appendChild($forgot_password_hint);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getAdHocInputs(): ?array{
		$f = __METHOD__;
		try {
			$print = false;
			$mode = $this->getAllocationMode();
			$splat = explode("/", request()->getRequestURI());
			$uri = $splat[count($splat) - 1];

			$c = $this->getActionAttribute() === '/activate' ? "first_login" : "regular";
			$ci = new HiddenInput($mode);
			$ci->setNameAttribute("context");
			$ci->setValueAttribute($c);

			$li = new HiddenInput($mode);
			$li->setNameAttribute("login_unset");
			$li->setValueAttribute(1);

			$ui = new HiddenInput($mode);
			$ui->setNameAttribute("uri");
			$ui->setValueAttribute($uri);

			$inputs = parent::getAdHocInputs();

			if (is_int($inputs)) {
				Debug::error("{$f} parent function returned an integer");
			} elseif (! is_array($inputs)) {
				Debug::error("{$f} parent function returned somehing that is not an array");
			}

			foreach ([
				$ci,
				$li,
				$ui
			] as $input) {
				$inputs[$input->getNameAttribute()] = $input;
			}

			$validator = new LenienthCaptchaValidator(LoginAttempt::class, 1);
			$mysqli = db()->getConnection(PublicReadCredentials::class);
			if (!$validator->validateFailedRequestCount($mysqli)){
				if($print){
					Debug::print("{$f} failed request count exceeds the number necessary to display hcaptcha");
				}
				$hcaptcha = new hCaptcha($mode, $this->getContext());
				$hcaptcha->setIdAttribute('login_hcaptcha');
				$inputs["hCaptcha"] = $hcaptcha;
			}elseif($print){
				Debug::print("{$f} insufficient number of failed requests to display captcha");
			}
			return $inputs;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function generateFormHeader(): void{
		$mode = $this->getAllocationMode();
		$radio_submit_login = new RadioButtonInput($mode);
		$radio_submit_login->setIdAttribute("radio_submit_login");
		$radio_submit_login->setNameAttribute("select_login_forgot");
		$radio_submit_login->setValueAttribute("login");
		$radio_submit_login->addClassAttribute("login_submit_check");
		$radio_submit_login->addClassAttribute("hidden");
		$radio_submit_login->setCheckedAttribute("checked");
		$radio_submit_login->setAttribute("tab", "login");
		$radio_forgot_password = new RadioButtonInput($mode);
		$radio_forgot_password->setIdAttribute("radio_forgot_password");
		$radio_forgot_password->setNameAttribute("select_login_forgot");
		$radio_forgot_password->setValueAttribute("forgot_password");
		$radio_forgot_password->addClassAttribute("login_forgot_check");
		$radio_forgot_password->addClassAttribute("hidden");
		$radio_forgot_password->setAttribute("tab", "forgot_password");
		$radio_forgot_name = new RadioButtonInput($mode);
		$radio_forgot_name->setIdAttribute("radio_forgot_name");
		$radio_forgot_name->setNameAttribute("select_login_forgot");
		$radio_forgot_name->setValueAttribute("forgot_name");
		$radio_forgot_name->addClassAttribute("login_forgot_name_check");
		$radio_forgot_name->addClassAttribute("hidden");
		$radio_forgot_name->setAttribute("tab", "forgot_name");
		$this->appendChild($radio_submit_login, $radio_forgot_name, $radio_forgot_password);
	}

	public static function getHoneypotCountArray(): ?array{
		return null;
		return [
			EmailAddressDatum::getColumnNameStatic() => 3
			// NameDatum::getColumnNameStatic() => 3,
			// PasswordDatum::getColumnNameStatic() => 3,
		];
	}

	/**
	 *
	 * @param InputElement $input
	 * @return int
	 */
	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try {
			$print = false;
			if ($input->hasAttribute("required")) {
				$input->removeAttribute("required");
			}
			$type = $input->getTypeAttribute();
			switch ($type) {
				case "checkbox":
				// case "text":
				// case "number":
				case "select":
					// case "textarea":
					// case "password":
					$div = new DivElement();
					$div->setStyleProperties([
						"position" => "relative",
						"display" => "block"
					]);
					$div->addClassAttribute("thumbsize", "relative", "block");
					$input->setWrapperElement($div);
					break;
				default:
					break;
			}
			$vn = $input->getColumnName();
			switch ($vn) {
				case "emailAddress":
					$ret = parent::reconfigureInput($input);
					$input->getWrapperElement()->setIdAttribute("login_email_container");
					$input->getWrapperElement()->setStyleProperties([
						"margin-bottom" => "2.5rem"
					]);
					return $ret;
				case "name":
					$input->setIdAttribute("login_username_field");
					$input->setLabelString(_("Username"));
					$input->setOnInputAttribute("loginUsernameInputHandler(event, this);");
					$ret = parent::reconfigureInput($input);
					$input->getWrapperElement()->setStyleProperties([
						"margin-bottom" => "2.5rem"
					]);
					$input->getWrapperElement()->setIdAttribute("login_username_container");
					$label = new LabelElement();
					$label->addClassAttribute("forgot_name_label");
					$label->setForAttribute("radio_forgot_name");
					$label->setInnerHTML(_("Forgot username"));
					$input->pushSuccessor($label);
					return $ret;
				case "password":
					$input->setIdAttribute("login_password_field");
					$placeholder = _("Password") . " (" . substitute(_("%1%+ characters"), 12) . ")";
					$input->setLabelString($placeholder);
					$input->setOnInputAttribute("loginPasswordInputHandler(event, this);");
					$ret = parent::reconfigureInput($input);
					$input->getWrapperElement()->setStyleProperties([
						"margin-bottom" => "2.5rem"
					]);
					$input->getWrapperElement()->setIdAttribute('login_password_container');
					$label = new LabelElement();
					$label->addClassAttribute("forgot_password_label");
					$label->setForAttribute("radio_forgot_password");
					$label->setInnerHTML(_("Forgot password"));
					$input->pushSuccessor($label);
					return $ret;
				default:
			}
			return parent::reconfigureInput($input);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function getLoginDirective(): string
	{
		return DIRECTIVE_LOGIN;
	}

	public function getDirectives(): ?array
	{
		return [
			$this->getLoginDirective(),
			DIRECTIVE_FORGOT_CREDENTIALS
		];
	}

	public function getFormDataIndices(): ?array
	{
		return [
			EmailAddressDatum::getColumnNameStatic() => EmailInput::class,
			NameDatum::getColumnNameStatic() => TextInput::class,
			PasswordDatum::getColumnNameStatic() => PasswordInput::class
		];
	}

	public static function getFormDispatchIdStatic(): ?string
	{
		return "login";
	}

	public static function getActionAttributeStatic(): ?string
	{
		if (hasInputParameter("refresh_uri")) {
			return getInputParameter('refresh_uri');
		}
		return request()->getRequestURI();
	}

	public function generateButtons(string $name): ?array
	{
		$f = __METHOD__;
		try {
			$mode = $this->getAllocationMode();
			$button = new ButtonInput($mode);
			$button->setNameAttribute("directive");
			$button->setValueAttribute($name);
			switch ($name) {
				case DIRECTIVE_LOGIN:
				case DIRECTIVE_ADMIN_LOGIN:
					$innerHTML = _("Log in");
					$button->setIdAttribute($this->getFormDispatchIdStatic() . "_button");
					if ($this->getFormDispatchIdStatic() === "login") {
						$button->setOnClickAttribute("loginButtonClicked(event, this);");
					} else {
						$button->setOnClickAttribute($button->getDefaultOnClickAttribute());
					}
					$button->setTypeAttribute("submit");
					break;
				case DIRECTIVE_FORGOT_CREDENTIALS:
					$innerHTML = _("Reset password");
					$button->setOnClickAttribute($button->getDefaultOnClickAttribute());
					$button->setTypeAttribute("submit");
					if (! $button->hasIdAttribute()) {
						// Debug::error("{$f} button lacks an ID attribute");
					}
					$button->setStyleProperties([
						'float' => 'none',
						'display' => 'inline-block',
						'position' => 'relative'
					]);
					break;
				default:
					Debug::error("{$f} invalid name attribute \"{$name}\"");
					return null;
			}
			$button->setInnerHTML($innerHTML);
			return [
				$button
			];
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getTemplateContextClass(): string
	{
		return config()->getNormalUserClass();
	}
}

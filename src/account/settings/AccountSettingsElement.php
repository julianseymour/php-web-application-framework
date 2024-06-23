<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\settings;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\avatar\ProfileImageThumbnailForm;
use JulianSeymour\PHPWebApplicationFramework\account\settings\display_name\DisplayNameOuterForm;
use JulianSeymour\PHPWebApplicationFramework\account\settings\timezone\TimezoneSettingsForm;
use JulianSeymour\PHPWebApplicationFramework\app\pwa\ProgressiveHyperlinkElement;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\settings\MfaSettingsForm;
use JulianSeymour\PHPWebApplicationFramework\auth\password\change\ChangePasswordForm;
use JulianSeymour\PHPWebApplicationFramework\auth\password\reset\PasswordResetOptionsForm;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\email\EmailNotificationSettingsForm;
use JulianSeymour\PHPWebApplicationFramework\email\change\ChangeEmailAddressForm;
use JulianSeymour\PHPWebApplicationFramework\input\RadioButtonInput;
use JulianSeymour\PHPWebApplicationFramework\notification\push\PushNotificationSettingsForm;
use JulianSeymour\PHPWebApplicationFramework\session\hijack\SessionHijackPreventionSettingsForm;
use JulianSeymour\PHPWebApplicationFramework\session\resume\SessionRecoveryCookie;
use JulianSeymour\PHPWebApplicationFramework\session\resume\SessionRecoveryData;
use JulianSeymour\PHPWebApplicationFramework\session\resume\SessionRecoverySettingsForm;
use JulianSeymour\PHPWebApplicationFramework\ui\MenuExpandingFormWrapper;
use Exception;

class AccountSettingsElement extends DivElement{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("slide_horizontal");
		$this->addClassAttribute("slide_settings");
	}

	public function getAccountSettingsOptions(): array{
		$context = $this->getContext();
		$mode = $this->getAllocationMode();
		$class1 = "menu_link";
		$class2 = "background_color_1";
		// account firewall
		$filter_link = new ProgressiveHyperlinkElement($mode);
		$href = '/account_firewall';
		$filter_link->setHrefAttribute($href);
		$filter_link->addClassAttribute($class1, $class2);
		$filter_link->setInnerHTML(_("Account firewall"));
		$ret = [
			$filter_link
		];
		$form_classes = [
			ProfileImageThumbnailForm::class,
			DisplayNameOuterForm::class,
			MfaSettingsForm::class,
			SessionHijackPreventionSettingsForm::class
		];
		foreach($form_classes as $form_class){
			$form = new MenuExpandingFormWrapper($mode);
			$form->setNestedFormClass($form_class);
			$form->bindContext($context);
			array_push($ret, $form);
		}

		$recovery_cookie = new SessionRecoveryCookie();
		$recovery_cookie->setReceptivity(DATA_MODE_SEALED);
		if($recovery_cookie->hasValidRecoveryData()){
			$srd = $recovery_cookie->getSessionRecoveryData();
		}else{
			$srd = new SessionRecoveryData();
		}
		deallocate($recovery_cookie);
		$srd->setUserData($context);
		$form = new MenuExpandingFormWrapper($mode);
		$form->setNestedFormClass(SessionRecoverySettingsForm::class);
		$form->bindContext($srd);
		array_push($ret, $form);

		$form_classes = [
			ChangePasswordForm::class,
			PasswordResetOptionsForm::class,
			ChangeEmailAddressForm::class,
			EmailNotificationSettingsForm::class,
			PushNotificationSettingsForm::class,
			// ThemeSettingsForm::class,
			TimezoneSettingsForm::class
		];
		foreach($form_classes as $form_class){
			$form = new MenuExpandingFormWrapper($mode);
			$form->setNestedFormClass($form_class);
			$form->bindContext($context);
			array_push($ret, $form);
		}

		return $ret;
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try{
			$mode = $this->getAllocationMode();
			$slide_deselect = new LabelElement($mode);
			$slide_deselect->addClassAttribute("slide_menu_label", "background_color_1", "slide_deselect");
			$slide_deselect->setForAttribute("menu_slide-none");
			$slide_deselect->setInnerHTML(_("Settings"));
			$this->appendChild($slide_deselect);
			$setlist = new DivElement($mode);
			$setlist->addClassAttribute("setlist", "background_color_1");
			$setlist->appendChild(...$this->getAccountSettingsOptions());
			$radio_settings_none = new RadioButtonInput($mode);
			$radio_settings_none->addClassAttribute("hidden");
			$radio_settings_none->setNameAttribute("radio_settings");
			$radio_settings_none->setIdAttribute("radio_settings_none");
			$setlist->appendChild($radio_settings_none);
			$this->appendChild($setlist);
			return $this->hasChildNodes() ? $this->getChildNodes() : [];
		}catch(Exception $x){
			x($f, $x);
		}
	}
}

<?php
namespace JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\CodeConfirmationAttempt;

class DefaultModule extends EmptyModule{

	public function getJavaScriptFunctionGeneratorClasses(): ?array{
		return [
			\JulianSeymour\PHPWebApplicationFramework\app\generator\AllocateDataStructuresJsFunctionGenerator::class,
			\JulianSeymour\PHPWebApplicationFramework\app\generator\GetCommandClassJsFunctionGenerator::class,
			\JulianSeymour\PHPWebApplicationFramework\app\generator\GetValidatorClassJsFunctionGenerator::class,
			\JulianSeymour\PHPWebApplicationFramework\app\generator\GetUseCaseClassJsFunctionGenerator::class,
			\JulianSeymour\PHPWebApplicationFramework\app\generator\HandleMessageEventJsFunctionGenerator::class,
			\JulianSeymour\PHPWebApplicationFramework\notification\GetTypedNotificationClassJsFunctionGenerator::class
		];
	}

	public function getUseCaseDictionary(): ?array{
		return [
			"account_firewall" => \JulianSeymour\PHPWebApplicationFramework\security\firewall\AccountFirewallUseCase::class,
			"activate" => \JulianSeymour\PHPWebApplicationFramework\account\activate\ValidateAccountActivationCodeUseCase::class,
			"admin_login" => \JulianSeymour\PHPWebApplicationFramework\admin\login\AdminLoginUseCase::class,
			"attach_file" => \JulianSeymour\PHPWebApplicationFramework\file\OpenEncryptedFileUseCase::class,
			"authorize_ip" => \JulianSeymour\PHPWebApplicationFramework\security\firewall\ValidateUnlistedIpAddressCodeUseCase::class,
			"blank" => \JulianSeymour\PHPWebApplicationFramework\use_case\BlankUseCase::class,
			"confirm_email" => \JulianSeymour\PHPWebApplicationFramework\email\change\ValidateChangeEmailCodeUseCase::class,
			"contact" => \JulianSeymour\PHPWebApplicationFramework\contact\ContactUsUseCase::class,
			"delete_cookies" => \JulianSeymour\PHPWebApplicationFramework\auth\cookie\DeleteCookiesUseCase::class,
			"error" => \JulianSeymour\PHPWebApplicationFramework\use_case\StatusCodeUseCase::class,
			"fetch_update" => \JulianSeymour\PHPWebApplicationFramework\notification\push\FetchNotificationUseCase::class,
			"files" => \JulianSeymour\PHPWebApplicationFramework\file\PublicFilesUseCase::class,
			// 'groups' => \JulianSeymour\PHPWebApplicationFramework\account\group\CCreateGroupUseCase::class,
			"image" => \JulianSeymour\PHPWebApplicationFramework\image\EncryptedImageUseCase::class,
			"images" => \JulianSeymour\PHPWebApplicationFramework\image\ImageNotFoundUseCase::class,
			// "initialize_location" => InitializeLocationUseCase::class,
			"nonexistent_uris" => \JulianSeymour\PHPWebApplicationFramework\security\nonexistent_uris\NonexistentUrisUseCase::class,
			"poll" => \JulianSeymour\PHPWebApplicationFramework\poll\ShortPollUseCase::class,
			'qr' => \JulianSeymour\PHPWebApplicationFramework\image\ShowQrCodeUseCase::class,
			"register" => \JulianSeymour\PHPWebApplicationFramework\account\register\RegisterAccountUseCase::class,
			"resend" => \JulianSeymour\PHPWebApplicationFramework\account\activate\ResendActivationEmailUseCase::class,
			"reset" => \JulianSeymour\PHPWebApplicationFramework\auth\password\reset\ValidateResetPasswordCodeUseCase::class,
			"robots.txt" => \JulianSeymour\PHPWebApplicationFramework\app\RobotsDotTxtUseCase::class,
			"script" => \JulianSeymour\PHPWebApplicationFramework\script\JavaScriptBundleUseCase::class,
			"server_cache" => \JulianSeymour\PHPWebApplicationFramework\cache\server\ClearServerCacheUseCase::class,
			"settings" => \JulianSeymour\PHPWebApplicationFramework\account\settings\AccountSettingsUseCase::class,
			"sitemap.xml" => \JulianSeymour\PHPWebApplicationFramework\app\SiteMapUseCase::class,
			"style" => \JulianSeymour\PHPWebApplicationFramework\style\CssBundleUseCase::class,
			"unlock" => \JulianSeymour\PHPWebApplicationFramework\account\lockout\ValidateLockoutCodeUseCase::class,
			// "update_server_keys" => RemoteServerKeysUseCase::class,
			"user_cache" => \JulianSeymour\PHPWebApplicationFramework\cache\user\ClearUserCacheUseCase::class,
			"validate" => \JulianSeymour\PHPWebApplicationFramework\validate\AjaxValidatorUseCase::class
		];
	}

	public function getClientCommandClasses(): ?array{
		return [
			\JulianSeymour\PHPWebApplicationFramework\command\debug\AlertCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\cache\CachePageContentCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\input\CheckInputCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\input\ClearInputCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\control\IfCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\form\InitializeAllFormsCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\func\DeferFunctionCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\DeleteElementCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\DocumentVisibilityStateCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\ElementExistsCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\debug\ErrorCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\FadeElementCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\input\FocusInputCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\GetAttributeCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\data\GetDataStructureCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\GetInnerHTMLCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\input\GetInputValueCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\GetOffsetHeightCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\GetOffsetWidthCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\security\captcha\hCaptchaRenderCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\ui\infobox\InfoBoxCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\form\InitializeFormCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\InsertElementCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\style\InsertStyleSheetCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\observer\IntersectionObserverCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\input\IsInputCheckedCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\IsScrolledIntoViewCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\debug\LogCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\NoOpCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\PushStateCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\ReidentifyElementCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\ReinsertElementCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\RemoveAttributeCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\session\timeout\ResetSessionTimeoutCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\poll\ScheduleUpdateCheckCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\ScrollIntoViewCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\SetAttributeCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\SetClassNameCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\SetInnerHTMLCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\input\SetInputValueCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\SetStylePropertiesCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\SetTextContentCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\form\SetUniversalFormActionCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\input\SoftDisableInputCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\command\element\UpdateElementCommand::class,
			\JulianSeymour\PHPWebApplicationFramework\account\online\UpdateOnlineStatusIndicatorCommand::class
		];
	}

	public function getValidatorClasses(): ?array{
		return [
			\JulianSeymour\PHPWebApplicationFramework\auth\password\ClientConfirmPasswordValidator::class,
			\JulianSeymour\PHPWebApplicationFramework\account\register\RegistrationEmailAddressValidator::class,
			\JulianSeymour\PHPWebApplicationFramework\account\register\RegistrationUsernameValidator::class
		];
	}

	public function getTemplateElementClasses(): ?array{
		return [
			\JulianSeymour\PHPWebApplicationFramework\account\avatar\Avatar::class,
			\JulianSeymour\PHPWebApplicationFramework\image\EncryptedImageElement::class,
			\JulianSeymour\PHPWebApplicationFramework\account\avatar\ProfileImageThumbnail::class
		];
	}

	public function getFormDataSubmissionClasses(): ?array{
		return [
			\JulianSeymour\PHPWebApplicationFramework\location\InitializeLocationForm::class
		];
	}

	public function getClientRenderedFormClasses(): ?array{
		return [
			\JulianSeymour\PHPWebApplicationFramework\location\InitializeLocationForm::class
		];
	}

	public function getGrantArray(): ?array{
		return [
			"reader-public" => [
				config()->getGuestUserClass() => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE
				],
				\JulianSeymour\PHPWebApplicationFramework\security\condemn\CondemnedIpAddress::class => [
					DIRECTIVE_INSERT
				],
				\JulianSeymour\PHPWebApplicationFramework\account\login\LoginAttempt::class => [
					DIRECTIVE_INSERT
				],
				\JulianSeymour\PHPWebApplicationFramework\notification\RetrospectiveNotificationData::class => [
					DIRECTIVE_INSERT
				],
				\JulianSeymour\PHPWebApplicationFramework\session\resume\SessionRecoveryData::class => [
					DIRECTIVE_INSERT,
					DIRECTIVE_DELETE
				],
				\JulianSeymour\PHPWebApplicationFramework\security\firewall\ListedIpAddress::class => [
					DIRECTIVE_INSERT
				],
				config()->getNormalUserClass() => [
					DIRECTIVE_UPDATE
				],
				"intersections.*" => [
					DIRECTIVE_INSERT
				]
			],
			"writer-admin" => [
				config()->getAdministratorClass() => [
					DIRECTIVE_UPDATE
				],
				config()->getGuestUserClass() => [
					DIRECTIVE_UPDATE
				],
				\JulianSeymour\PHPWebApplicationFramework\email\DummyEmail::class => [
					DIRECTIVE_INSERT
				],
				\JulianSeymour\PHPWebApplicationFramework\image\ImageData::class => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE,
					DIRECTIVE_DELETE
				],
				\JulianSeymour\PHPWebApplicationFramework\auth\mfa\InvalidatedOtp::class => [
					DIRECTIVE_INSERT
				],
				\JulianSeymour\PHPWebApplicationFramework\security\nonexistent_uris\NonexistentUriData::class => [
					DIRECTIVE_UPDATE,
					DIRECTIVE_DELETE
				],
				\JulianSeymour\PHPWebApplicationFramework\account\activate\PreActivationConfirmationCode::class => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE
				],
				\JulianSeymour\PHPWebApplicationFramework\notification\RetrospectiveNotificationData::class => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE
				],
				\JulianSeymour\PHPWebApplicationFramework\session\resume\SessionRecoveryData::class => [
					DIRECTIVE_INSERT,
					DIRECTIVE_DELETE
				],
				\JulianSeymour\PHPWebApplicationFramework\security\firewall\ListedIpAddress::class => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE,
					DIRECTIVE_DELETE
				],
				\JulianSeymour\PHPWebApplicationFramework\security\firewall\UnlistedIpAddressConfirmationCode::class => [
					DIRECTIVE_INSERT
				],
				\JulianSeymour\PHPWebApplicationFramework\account\UsernameData::class => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE
				],
				"intersections.*" => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE,
					DIRECTIVE_DELETE
				]
			],
			"writer-public" => [
				config()->getGuestUserClass() => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE
				],
				CodeConfirmationAttempt::class => [
					DIRECTIVE_INSERT
				],
				\JulianSeymour\PHPWebApplicationFramework\contact\ContactusEmail::class => [
					DIRECTIVE_INSERT
				],
				\JulianSeymour\PHPWebApplicationFramework\image\ImageData::class => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE,
					DIRECTIVE_DELETE
				],
				\JulianSeymour\PHPWebApplicationFramework\auth\mfa\InvalidatedOtp::class => [
					DIRECTIVE_INSERT
				],
				\JulianSeymour\PHPWebApplicationFramework\security\firewall\ListedIpAddress::class => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE,
					DIRECTIVE_DELETE
				],
				\JulianSeymour\PHPWebApplicationFramework\account\lockout\LockoutWaiverAttempt::class => [
					DIRECTIVE_INSERT
				],
				\JulianSeymour\PHPWebApplicationFramework\account\login\LoginAttempt::class => [
					DIRECTIVE_INSERT
				],
				\JulianSeymour\PHPWebApplicationFramework\security\nonexistent_uris\NonexistentUriData::class => [
					DIRECTIVE_INSERT
				],
				\JulianSeymour\PHPWebApplicationFramework\notification\RetrospectiveNotificationData::class => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE
				],
				\JulianSeymour\PHPWebApplicationFramework\account\activate\PreActivationConfirmationCode::class => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE
				],
				\JulianSeymour\PHPWebApplicationFramework\notification\push\PushSubscriptionData::class => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE,
					DIRECTIVE_DELETE
				],
				\JulianSeymour\PHPWebApplicationFramework\session\resume\SessionRecoveryData::class => [
					DIRECTIVE_INSERT,
					DIRECTIVE_DELETE
				],
				\JulianSeymour\PHPWebApplicationFramework\email\DummyEmail::class => [
					DIRECTIVE_INSERT
				],
				\JulianSeymour\PHPWebApplicationFramework\security\firewall\UnlistedIpAddressConfirmationCode::class => [
					DIRECTIVE_INSERT
				],
				\JulianSeymour\PHPWebApplicationFramework\account\UsernameData::class => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE
				],
				config()->getNormalUserClass() => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE
				],
				"events.*" => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE
				],
				"intersections.*" => [
					DIRECTIVE_INSERT,
					DIRECTIVE_UPDATE,
					DIRECTIVE_DELETE
				]
			]
		];
	}

	public function getDataStructureClasses(): ?array{
		return [
			\JulianSeymour\PHPWebApplicationFramework\account\UsernameData::class,
			\JulianSeymour\PHPWebApplicationFramework\image\ImageData::class,
			[
				config()->getAdministratorClass(),
				config()->getNormalUserClass(),
				config()->getGuestUserClass(),
				config()->getShadowUserClass()
			],
			[
				\JulianSeymour\PHPWebApplicationFramework\language\MultilingualNameData::class,
				\JulianSeymour\PHPWebApplicationFramework\data\SharedStringData::class
			],
			\JulianSeymour\PHPWebApplicationFramework\contact\ContactUsEmail::class,
			\JulianSeymour\PHPWebApplicationFramework\db\credentials\EncryptedDatabaseCredentials::class,
			\JulianSeymour\PHPWebApplicationFramework\auth\mfa\InvalidatedOtp::class,
			[
				\JulianSeymour\PHPWebApplicationFramework\security\condemn\CondemnedIpAddress::class,
				\JulianSeymour\PHPWebApplicationFramework\security\firewall\ListedIpAddress::class
			],
			[
				\JulianSeymour\PHPWebApplicationFramework\account\activate\PreActivationConfirmationCode::class,
				\JulianSeymour\PHPWebApplicationFramework\security\firewall\UnlistedIpAddressConfirmationCode::class
			],
			[
				\JulianSeymour\PHPWebApplicationFramework\account\login\LoginAttempt::class,
				\JulianSeymour\PHPWebApplicationFramework\account\activate\ActivationAttempt::class,
				\JulianSeymour\PHPWebApplicationFramework\auth\ReauthenticationEvent::class
			],
			\JulianSeymour\PHPWebApplicationFramework\session\resume\SessionRecoveryData::class,
			\JulianSeymour\PHPWebApplicationFramework\notification\push\PushSubscriptionData::class,
			\JulianSeymour\PHPWebApplicationFramework\email\DummyEmail::class,
			\JulianSeymour\PHPWebApplicationFramework\app\ServerKeypair::class,
			\JulianSeymour\PHPWebApplicationFramework\notification\RetrospectiveNotificationData::class
		];
	}

	public function getCascadingStyleSheetFilePaths(): ?array{
		return [
			// FRAMEWORK_INSTALL_DIRECTORY."/css/css_session_timeout_overlay.php",
			\JulianSeymour\PHPWebApplicationFramework\ui\CloseMenuLabel::getStyleSheetPath(),
			\JulianSeymour\PHPWebApplicationFramework\ui\HamburgerMenuLabelElement::getStyleSheetPath(),
			\JulianSeymour\PHPWebApplicationFramework\ui\infobox\InfoBoxElement::getStyleSheetPath(),
			\JulianSeymour\PHPWebApplicationFramework\notification\ui\NotificationListElement::getStyleSheetPath(),
			FRAMEWORK_INSTALL_DIRECTORY . "/search/search.css",
			// FRAMEWORK_INSTALL_DIRECTORY."/css/style-edituser.css",
			FRAMEWORK_INSTALL_DIRECTORY . "/ui/style-expander.css",
			FRAMEWORK_INSTALL_DIRECTORY . "/security/firewall/style-firewall.css",
			FRAMEWORK_INSTALL_DIRECTORY . "/ui/style-footer.css",
			// FRAMEWORK_INSTALL_DIRECTORY."/css/style-hover.css",
			\JulianSeymour\PHPWebApplicationFramework\ui\infobox\InfoBoxElement::getStyleSheetPath(),
			FRAMEWORK_INSTALL_DIRECTORY . "/security/firewall/style-list_ip.css",
			FRAMEWORK_INSTALL_DIRECTORY . "/account/login/style-login.css",
			FRAMEWORK_INSTALL_DIRECTORY . "/ui/style-main.css",
			FRAMEWORK_INSTALL_DIRECTORY . "/account/settings/style-settings.css",
			\JulianSeymour\PHPWebApplicationFramework\auth\mfa\settings\MfaQrCodeElement::getStyleSheetPath(),
			FRAMEWORK_INSTALL_DIRECTORY . "/ui/style-tabs.css",
			FRAMEWORK_INSTALL_DIRECTORY . "/session/timeout/style-timeout.css",
			FRAMEWORK_INSTALL_DIRECTORY . "/ui/WidgetContainer.css",
			\JulianSeymour\PHPWebApplicationFramework\ui\YouAreLoggedInAsElement::getStyleSheetPath(),
			\JulianSeymour\PHPWebApplicationFramework\input\FancyCheckbox::getStyleSheetPath(),
			\JulianSeymour\PHPWebApplicationFramework\input\FancyRadioButton::getStyleSheetPath(),
			\JulianSeymour\PHPWebApplicationFramework\account\avatar\Avatar::getStyleSheetPath(),
			\JulianSeymour\PHPWebApplicationFramework\form\AjaxForm::getStyleSheetPath(),
			\JulianSeymour\PHPWebApplicationFramework\input\ToggleInput::getStyleSheetPath()
		];
	}

	public function getValidMimeTypes(): array{
		return [
			MIME_TYPE_GIF,
			MIME_TYPE_JPEG,
			MIME_TYPE_OCTET_STREAM,
			MIME_TYPE_PLAINTEXT,
			MIME_TYPE_PNG,
			MIME_TYPE_7ZIP,
			MIME_TYPE_BZIP2,
			MIME_TYPE_GZIP,
			MIME_TYPE_PDF,
			MIME_TYPE_RAR,
			MIME_TYPE_ZIP
		];
	}

	public function getValidDirectives(): array{
		return [
			DIRECTIVE_ADMIN_LOGIN,
			DIRECTIVE_DELETE,
			DIRECTIVE_DELETE_FOREIGN,
			DIRECTIVE_DOWNLOAD,
			DIRECTIVE_EMAIL_CONFIRMATION_CODE,
			DIRECTIVE_FORGOT_CREDENTIALS,
			DIRECTIVE_IMPORT_CSV,
			DIRECTIVE_INSERT,
			DIRECTIVE_LANGUAGE,
			DIRECTIVE_LOGIN,
			DIRECTIVE_LOGOUT,
			DIRECTIVE_MASS_DELETE,
			DIRECTIVE_MFA,
			DIRECTIVE_PROCESS,
			DIRECTIVE_READ,
			DIRECTIVE_READ_MULTIPLE,
			DIRECTIVE_REGENERATE,
			DIRECTIVE_SEARCH,
			DIRECTIVE_SUBMIT,
			DIRECTIVE_UNSET,
			DIRECTIVE_UPDATE,
			DIRECTIVE_UPLOAD,
			DIRECTIVE_VALIDATE
		];
	}

	public function getClientConstants(): ?array{
		$f = __METHOD__;
		$ret = [
			'WEBSITE_DOMAIN' => WEBSITE_DOMAIN,
			// public key for push API
			'PUSH_API_SERVER_PUBLIC_KEY' => PUSH_API_SERVER_PUBLIC_KEY,
			// session timeout duration
			'SESSION_TIMEOUT_SECONDS' => SESSION_TIMEOUT_SECONDS,
			// status codes
			'SUCCESS' => SUCCESS,
			'ERROR_LOGIN_CREDENTIALS' => ERROR_LOGIN_CREDENTIALS,
			'ERROR_XSRF' => ERROR_LOGIN_CREDENTIALS,
			'RESULT_ACTIVATE_SUCCESS' => RESULT_ACTIVATE_SUCCESS,
			'RESULT_BFP_MFA_CONFIRM' => RESULT_ACTIVATE_SUCCESS,
			'RESULT_LOGGED_OUT' => RESULT_LOGGED_OUT,
			'RESULT_REGISTER_SUCCESS' => RESULT_LOGGED_OUT,
			'RESULT_RESET_SUBMIT' => RESULT_RESET_SUBMIT,
			'RESULT_SETTINGS_UPDATED' => RESULT_SETTINGS_UPDATED,
			'RESULT_SUBMISSION_ACCEPTED' => RESULT_SUBMISSION_ACCEPTED,
			'RESULT_BFP_MFA_FAILED' => RESULT_BFP_MFA_FAILED,
			// account types
			'ACCOUNT_TYPE_ERROR' => ACCOUNT_TYPE_ERROR,
			'ACCOUNT_TYPE_ADMIN' => ACCOUNT_TYPE_ADMIN,
			'ACCOUNT_TYPE_USER' => ACCOUNT_TYPE_USER,
			'ACCOUNT_TYPE_GUEST' => ACCOUNT_TYPE_GUEST,
			'ACCOUNT_TYPE_DEVELOPER' => ACCOUNT_TYPE_DEVELOPER,
			'ACCOUNT_TYPE_TRANSLATOR' => ACCOUNT_TYPE_TRANSLATOR,
			'ACCOUNT_TYPE_HELPDESK' => ACCOUNT_TYPE_HELPDESK,
			// datatypes
			'DATATYPE_FILE' => DATATYPE_FILE,
			'DATATYPE_NOTIFICATION' => DATATYPE_NOTIFICATION,
			// directives
			'DIRECTIVE_INSERT' => DIRECTIVE_INSERT,
			'DIRECTIVE_UPDATE' => DIRECTIVE_UPDATE,
			'DIRECTIVE_DELETE' => DIRECTIVE_DELETE,
			'DIRECTIVE_IMPORT_CSV' => DIRECTIVE_IMPORT_CSV,
			'DIRECTIVE_REGENERATE' => DIRECTIVE_REGENERATE,
			'DIRECTIVE_UNSET' => DIRECTIVE_UNSET,
			'DIRECTIVE_DELETE_FOREIGN' => DIRECTIVE_DELETE_FOREIGN,
			'DIRECTIVE_EMAIL_CONFIRMATION_CODE' => DIRECTIVE_EMAIL_CONFIRMATION_CODE,
			'DIRECTIVE_READ' => DIRECTIVE_READ,
			'DIRECTIVE_READ_MULTIPLE' => DIRECTIVE_READ_MULTIPLE,
			'DIRECTIVE_REFRESH_SESSION' => DIRECTIVE_REFRESH_SESSION,
			'DIRECTIVE_SEARCH' => DIRECTIVE_SEARCH,
			'DIRECTIVE_SELECT' => DIRECTIVE_SELECT,
			'DIRECTIVE_NONE' => DIRECTIVE_NONE,
			'DIRECTIVE_SUBMIT' => DIRECTIVE_SUBMIT,
			'DIRECTIVE_UPLOAD' => DIRECTIVE_UPLOAD,
			'DIRECTIVE_VALIDATE' => DIRECTIVE_VALIDATE,
			// image max dimension
			'IMAGE_MAX_DIMENSION' => IMAGE_MAX_DIMENSION,
			// key generation modes
			'KEY_GENERATION_MODE_PSEUDOKEY' => IMAGE_MAX_DIMENSION,
			'KEY_GENERATION_MODE_HASH' => KEY_GENERATION_MODE_HASH,
			'KEY_GENERATION_MODE_LITERAL' => KEY_GENERATION_MODE_LITERAL,
			'KEY_GENERATION_MODE_NATURAL' => KEY_GENERATION_MODE_NATURAL,
			//notifications types
			'NOTIFICATION_TYPE_SECURITY' => NOTIFICATION_TYPE_SECURITY,
			// online status codes
			'ONLINE_STATUS_UNDEFINED' => ONLINE_STATUS_UNDEFINED,
			'ONLINE_STATUS_NONE' => ONLINE_STATUS_NONE,
			'ONLINE_STATUS_OFFLINE' => ONLINE_STATUS_OFFLINE,
			'ONLINE_STATUS_ONLINE' => ONLINE_STATUS_ONLINE,
			'ONLINE_STATUS_APPEAR_OFFLINE' => ONLINE_STATUS_APPEAR_OFFLINE,
			'ONLINE_STATUS_AWAY' => ONLINE_STATUS_AWAY,
			'ONLINE_STATUS_BUSY' => ONLINE_STATUS_BUSY,
			'ONLINE_STATUS_CUSTOM' => ONLINE_STATUS_CUSTOM,
			// replacement effects
			'EFFECT_NONE' => EFFECT_NONE,
			'EFFECT_FADE' => EFFECT_FADE,
			// application integreation mode
			'APPLICATION_INTEGRATION_MODE' => APPLICATION_INTEGRATION_MODE,
			// some strings that need to be defined for certain client side features to work
			"STRING_APPEAR_OFFLINE" => _("Appear offline"),
			"STRING_AWAY" => _("Away"),
			"STRING_BUSY" => _("Busy"),
			"STRING_IMPORT_CSV_FILES" => _("Import CSV files"),
			"STRING_NOTIFICATION_DISMISSAL_ERROR" => _("Notification dismissal error"),
			"STRING_OFFLINE" => _("Offline"),
			"STRING_ONLINE" => _("Online"),
			"STRING_PASSWORDS_MUST_MATCH" => _("Passwords must match"),
			"STRING_READ" => _("Read"),
			"STRING_REFRESH_SESSION" => _("Refresh session"),
			"STRING_SEARCH" => _("Search"),
			"STRING_SECURITY" => _("Security"),
			"STRING_SELECT" => _("Select"),
			"STRING_SEND_CONFIRMATION_CODE" => _("Send confirmation code"),
			"STRING_SUBMIT" => _("Submit"),
			"STRING_UPDATE" => _("Update"),
			"STRING_UPLOAD" => _("Upload"),
			"STRING_VALIDATE" => _("Validate")
		];
		if (defined('HCAPTCHA_SITE_KEY')) {
			$ret['HCAPTCHA_SITE_KEY'] = HCAPTCHA_SITE_KEY;
		}
		return $ret;
	}

	public function getServiceWorkerDependencyFilePaths(): ?array{
		$f = __METHOD__;
		return [
			FRAMEWORK_INSTALL_DIRECTORY . "/common/common.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/common/locutus.js",
			FRAMEWORK_INSTALL_DIRECTORY . '/core/Basic.js',
			FRAMEWORK_INSTALL_DIRECTORY . '/app/ResponseText.js',
			FRAMEWORK_INSTALL_DIRECTORY . '/app/ResponseProperty.js',
			FRAMEWORK_INSTALL_DIRECTORY . '/data/DataStructure.js',
			FRAMEWORK_INSTALL_DIRECTORY . '/account/owner/UserOwned.js',
			FRAMEWORK_INSTALL_DIRECTORY . '/account/correspondent/UserCorrespondence.js',
			\JulianSeymour\PHPWebApplicationFramework\notification\NotificationData::getJavaScriptClassPath(),
			FRAMEWORK_INSTALL_DIRECTORY . '/security/SecurityNotificationData.js'
		];
	}

	public function getJavaScriptFilePaths(): ?array{
		return [
			FRAMEWORK_INSTALL_DIRECTORY . "/core/Basic.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/app/ResponseProperty.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/data/DataStructure.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/account/owner/UserOwned.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/account/correspondent/UserCorrespondence.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/form/AjaxForm.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/common/locutus.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/common/common.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/cache/cache.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/ui/WidgetIcon.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/file/EncryptedFile.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/image/EncryptedImage.js",
			FRAMEWORK_INSTALL_DIRECTORY . '/location/location.js',
			FRAMEWORK_INSTALL_DIRECTORY . "/account/login/login.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/ui/Menu.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/datum/NameDatum.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/account/register/register.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/search/search.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/account/settings/settings.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/language/translate/translate.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/command/Command.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/command/element/ElementCommand.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/command/element/MultipleElementCommand.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/command/control/ControlStatementCommand.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/command/func/InvokeFunctionCommand.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/command/element/AttributeCommand.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/validate/Validator.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/app/ResponseText.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/use_case/UseCase.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/search/SearchUseCase.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/poll/ShortPollUseCase.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/poll/ShortPollForm.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/auth/mfa/MfaUseCase.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/account/login/LoginUseCase.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/account/logout/LogoutUseCase.js",
			FRAMEWORK_INSTALL_DIRECTORY . "/input/choice/Choice.js",
			\JulianSeymour\PHPWebApplicationFramework\notification\NotificationData::getJavaScriptClassPath(),
			FRAMEWORK_INSTALL_DIRECTORY . "/notification/push/push.js",
			\JulianSeymour\PHPWebApplicationFramework\ui\infobox\InfoBoxElement::getJavaScriptClassPath()
		];
	}

	public function getReservedRoles(): array{
		return [
			USER_ROLE_BUYER,
			USER_ROLE_COLLECTOR,
			USER_ROLE_DEBTOR,
			USER_ROLE_ERROR,
			USER_ROLE_FOUNDER,
			USER_ROLE_HOST,
			USER_ROLE_MEMBER,
			USER_ROLE_PARIAH,
			USER_ROLE_RECIPIENT,
			USER_ROLE_SELLER,
			USER_ROLE_SENDER,
			USER_ROLE_STRANGER,
			USER_ROLE_VISITOR
		];
	}

	public function getClientDataStructureClasses(): ?array{
		return [
			DATATYPE_FILE => [
				FILE_TYPE_ENCRYPTED => \JulianSeymour\PHPWebApplicationFramework\file\EncryptedFile::class
			],
			DATATYPE_NOTIFICATION => mods()->getTypedNotificationClasses()
		];
	}

	public function getClientUseCaseDictionary(): ?array{
		return [
			"history_search" => "HistorySearchUseCase",
			"login" => "LoginUseCase",
			"logout" => "LogoutUseCase",
			"mfa" => "MfaUseCase",
			"search" => "SearchUseCase",
			"poll" => "ShortPollUseCase"
		];
	}

	public function getInvokeableJavaScriptFunctions(): ?array{
		return [
			"alert" => "window.alert",
			"elementExists" => "elementExists",
			"enable" => "enable",
			"fetch" => "handleFetchEvent",
			"handleFetchEvent" => "handleFetchEvent",
			"info" => "InfoBoxElement.showInfoBox",
			'initializeAllForms' => 'AjaxForm.initializeAllForms',
			"reset_timeout" => "resetSessionTimeoutAnimation",
			"resetSessionTimeoutAnimation" => "resetSessionTimeoutAnimation",
			"showInfoBox" => "InfoBoxElement.showInfoBox"
		];
	}

	public function getMessageEventHandlerCases(): ?array{
		$break = CommandBuilder::break();
		$info = new \JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand("response.info");
		return [
			"window.alert" => [],
			"alert" => [
				CommandBuilder::call("alert", $info),
				$break
			],
			/*"beep" => [
				CommandBuilder::call("beep"),
				$break
			],*/
			"handle_fetch" => [],
			"fetch" => [
				CommandBuilder::call("handleFetchEvent", new \JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand("response")),
				$break
			],
			"InfoBoxElement.showInfoBox" => [],
			"showInfoBox" => [],
			"info" => [
				CommandBuilder::call("info", $info),
				$break
			],
			"reset_timeout" => [
				CommandBuilder::call("resetSessionTimeoutAnimation", false),
				$break
			]
		];
	}
	
	public function getTypedNotificationClasses(): ?array{
		return [
			\JulianSeymour\PHPWebApplicationFramework\security\SecurityNotificationData::class
		];
	}
}
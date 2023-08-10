<?php
namespace JulianSeymour\PHPWebApplicationFramework\error;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\login\FullAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\language\Internationalization;
use JulianSeymour\PHPWebApplicationFramework\language\settings\LanguageSettingsSessionData;
use Exception;

abstract class ErrorMessage
{

	public static function getVisualNotice($t)
	{
		$div = new DivElement();
		$div->addClassAttribute("visual_result_container", "background_color_2");
		// $div->addClassAttribute("shadow");
		$div->setInnerHTML($t);
		return $div;
	}

	public static function getVisualError($status)
	{
		return static::getVisualNotice(ErrorMessage::getResultMessage($status));
	}

	public static function getInfoBoxStatus($status)
	{
		return static::getInfoBoxElement(static::getVisualError($status));
	}

	/**
	 * shortcut fatal error for unimplemented functions
	 *
	 * @param string $f
	 */
	public static function unimplemented($f)
	{
		Debug::error("{$f}: " . ErrorMessage::getResultMessage(ERROR_NOT_IMPLEMENTED));
	}

	public static function deprecated($f)
	{
		Debug::error("{$f}: " . ErrorMessage::getResultMessage(ERROR_DEPRECATED));
	}

	public static function getResultMessage($status, $language_id = null)
	{
		$f = __METHOD__; //ErrorMessage::class . "::getResultMessage()";
		try {
			switch ($status) {
				/*case (ERROR_MISSING_VARIABLE):
					return Internationalization::chainTranslate($language_id, STRING_X_FAILED, STRING_OPERATION);
				case (ERROR_MYSQL_QUERY):
					return Internationalization::translate(STRING_FAILED_DATABASE_QUERY, $language_id);
				case (ERROR_MYSQL_RESULT):
					return Internationalization::translate(STRING_FAILED_DATABASE_RESULT, $language_id);
				case (ERROR_XSRF):
				case ERROR_SESSION_EXPIRED:
					return Internationalization::translate(STRING_SESSION_EXPIRED, $language_id);
				case (ERROR_MYSQL_EXECUTE):
					return Internationalization::translate(STRING_FAILED_DATABASE_EXECUTE, $language_id);
				case (ERROR_MYSQL_BIND):
					return Internationalization::translate(STRING_FAILED_DATABASE_BIND, $language_id);
				case (ERROR_MYSQL_PREPARE):
					return Internationalization::translate(STRING_FAILED_DATABASE_PREPARE, $language_id);
				case ERROR_MYSQL_CONNECT:
				case (ERROR_MYSQL_CONNECT):
					return Internationalization::translate(STRING_FAILED_DATABASE_CONNECT, $language_id);
				case (ERROR_INTERNAL):
				case ERROR_HONEYPOT:
					return Internationalization::translate(STRING_CANNOT_PROCESS_REQUEST, $language_id);
				case (ERROR_INPUT_BLANK):
					return Internationalization::translate(STRING_INPUT_BLANK, $language_id);
				case ERROR_USER_NOT_FOUND:
				case ERROR_USER_NOT_FOUND_GET_EMAIL:
				case ERROR_LOGIN_CREDENTIALS:
					return Internationalization::chainTranslate($language_id, STRING_INVALID_X, STRING_CREDENTIALS);
				case (ERROR_ALREADY_ACTIVE):
					return Internationalization::translate(STRING_ALREADY_ACTIVE, $language_id);
				case (ERROR_ACCOUNT_INACTIVE):
					return Internationalization::translate(STRING_ACCOUNT_INACTIVE, $language_id);
				case (ERROR_ALREADY_LOGGED):
					return Internationalization::translate(STRING_ALREADY_LOGGED, $language_id);
				case (RESULT_LOGGED_OUT):
					return Internationalization::translate(STRING_LOGGED_OUT, $language_id);
				case (SUCCESS):
					Debug::printStackTraceNoExit("{$f} success");
					return Internationalization::translate(STRING_REQUEST_PROCESSED, $language_id);
				case (ERROR_YOUR_FAULT):
					return Internationalization::translate(STRING_ERROR_PROCESSING_REQUEST, $language_id);
				case (RESULT_RESET_SUBMIT):
					return Internationalization::translate(STRING_RESET_SUBMIT, $language_id);
				case RESULT_RESET_SUCCESS:
					return Internationalization::translate(STRING_PASSWORD_RESET, $language_id);
				case RESULT_CHANGEPASS_SUCCESS:
					return Internationalization::translate(STRING_PASSWORD_UPDATED_PLEASE_REFRESH, $language_id);
				case ERROR_CHANGEPASS_MISSING:
					return Internationalization::chainTranslate($language_id, STRING_INVALID_X, STRING_PASSWORD);
				case ERROR_IP_ADDRESS_BLOCKED_BY_USER:
					return Internationalization::translate(STRING_X_BY_Y, $language_id, Internationalization::translate(STRING_SINGULAR_NOUN_X_PAST_TENSE_VERB_Y, $language_id, Internationalization::translate(STRING_IP_ADDRESS, $language_id), Internationalization::translate(STRING_BLOCKED, $language_id)), Internationalization::translate(STRING_USER, $language_id));
				case ERROR_NOT_IMPLEMENTED:
					$session = new LanguageSettingsSessionData();
					$language = $session->getLanguageCode();
					if ($language === null || $language === LANGUAGE_UNDEFINED) {
						$language = LANGUAGE_DEFAULT;
					}
					return Internationalization::translate(STRING_SERVER_MAINTENANCE, $language);
				case RESULT_CHANGEMAIL_SUCCESS:
					return Internationalization::translate(STRING_EMAIL_UPDATED, $language_id);
				case (ERROR_LINK_EXPIRED):
				case ERROR_RESET_DELETION:
				case (ERROR_BADCODE):
					return Internationalization::translate(STRING_LINK_EXPIRED, $language_id);
				case ERROR_CHECKOUT_IMEI_TAMPER:
					return Internationalization::translate(STRING_IMEI_MODE_UNDEFINED, $language_id);
				case ERROR_CHECKOUT_AUTO_REJECT_ACCESS:
					return Internationalization::chainTranslate($language_id, STRING_X_UNAUTHORIZED, STRING_TRANSACTION);
				case RESULT_CHECKOUT_CONFIRM:
					return Internationalization::chainTranslate($language_id, STRING_CONFIRM_X, STRING_TRANSACTION);
				case ERROR_BASE_SVCKEY:
					return Internationalization::chainTranslate($language_id, STRING_X_IS_UNDEFINED, STRING_X_KEY, STRING_BASE_X, STRING_SERVICE);
				case ERROR_DISPATCH_NOTHING:
					return Internationalization::translate(STRING_NOTHING_TO_REPORT, $language_id);
				case ERROR_KEY_UNDEFINED:
					return Internationalization::chainTranslate($language_id, STRING_X_IS_UNDEFINED, STRING_KEY);
				case ERROR_MUST_LOGIN:
					return Internationalization::translate(STRING_MUST_LOGIN, $language_id);
				case ERROR_PROCESS_DATATYPE:
					return Internationalization::chainTranslate($language_id, STRING_INVALID_X, STRING_DATATYPE);
				case ERROR_IDENTICAL_NAME:
					return Internationalization::translate(STRING_IDENTICAL_NAME, $language_id);
				case ERROR_NULL_PARENTKEY:
					return Internationalization::chainTranslate($language_id, STRING_NULL_X, STRING_X_KEY, STRING_PARENT);
				case ERROR_NULL_DESCRIPTION:
					return Internationalization::translate(STRING_NULL_DESCRIPTION, $language_id);
				case ERROR_NOT_FOUND:
					return Internationalization::chainTranslate($language_id, STRING_X_NOT_FOUND, STRING_OBJECT);
				case ERROR_NOT_MAPPED:
					return Internationalization::translate(STRING_KEY_UNMAPPED, $language_id);
				case ERROR_NAME_LENGTH:
					return Internationalization::translate(STRING_USERNAME_LENGTH, $language_id);
				case ERROR_REGISTER_PASSWORD_WEAK:
					return Internationalization::translate(STRING_PASSWORD_WEAK, $language_id);
				case ERROR_REGISTER_NULL_CONFIRM:
					return Internationalization::translate(STRING_PASSWORD_CONFIRMATION_REQURED, $language_id);
				case ERROR_PASSWORD_UNDEFINED:
					return Internationalization::translate(STRING_PASSWORD_SUBMITTED_EMPTY, $language_id);
				case ERROR_CHANGEMAIL_BADCODE:
					return Internationalization::translate(STRING_CONFIRMATION_CODE_MISMATCH, $language_id);
				case ERROR_PASSWORD_MISMATCH:
					return Internationalization::translate(STRING_PASSWORDS_MISMATCH, $language_id);
				case ERROR_NAME_UNDEFINED:
					return Internationalization::chainTranslate($language_id, STRING_X_UNDEFINED, STRING_NAME);
				case ERROR_CHECKOUT_REPEAT:
					return Internationalization::translate(STRING_TRANSACTION_REJECTED_REPEAT, $language_id);
				case ERROR_CHECKOUT_IDK_QTY:
					return Internationalization::translate(STRING_MUST_PROVIDE_QUANTITY, $language_id);
				case ERROR_CHECKOUT_IMEI_SINGLE:
					return Internationalization::translate(STRING_MUST_PROVIDE_IMEI, $language_id);
				case ERROR_CHECKOUT_MODE:
					return Internationalization::translate(STRING_IMEI_MODE_UNDEFINED, $language_id);
				case ERROR_CHECKOUT_EMPTY:
					return Internationalization::translate(STRING_CHECKOUT_EMPTY, $language_id);
				case ERROR_CHECKOUT_TAMPER:
					return Internationalization::translate(STRING_TRANSACTION_BLOCKED_TAMPER_POST, $language_id);
				case ERROR_PRICE_0CREDITS:
					return Internationalization::translate(STRING_PRICE_0CREDITS, $language_id);
				case ERROR_NULL_ITERATOR:
					return Internationalization::translate(STRING_CHECKOUT_NULL_ITERATOR, $language_id);
				case ERROR_NULL_TEMPLATE:
					return Internationalization::translate(STRING_ACCOUNT_PENDING_REVIEW, $language_id);
				case ERROR_CHECKOUT_IMEI_REQUIRED:
					return Internationalization::chainTranslate($language_id, STRING_X_REQUIRED, STRING_IMEI);
				case RESULT_DELETION_SUCCESSFUL:
					return Internationalization::translate(STRING_DELETION_SUCCESSFUL, $language_id);
				case ERROR_NULL_SERVICE:
					return Internationalization::translate(STRING_NULL_SERVICE, $language_id);
				case ERROR_NULL_IMPLICIT_SVC:
					return Internationalization::translate(STRING_IMPLICIT_SERVICE_UNDEFINED, $language_id);
				case ERROR_CHANGEMAIL_ROWCOUNT:
				case ERROR_DUPLICATE_ENTRY:
					return Internationalization::translate(STRING_DUPLICATE_ENTRY, $language_id);
				case ERROR_KEY_INHERITED:
					return Internationalization::translate(STRING_PARENT_CHILD_SAME_KEY, $language_id);
				case ERROR_TRANSACTION_STATE:
					return Internationalization::translate(STRING_ERROR_UPDATING_TRANSACTION_STATE, $language_id);
				case RESULT_TRANSACTION_UPDATED:
					return Internationalization::translate(STRING_TRANSACTION_UPDATE_SUCCESSFUL, $language_id);
				case ERROR_DEADBEAT:
					return Internationalization::translate(STRING_INSUFFICIENT_BALANCE, $language_id);
				case RESULT_CHANGESETTINGS_SUCCESS:
					return Internationalization::translate(STRING_SETTINGS_UPDATED, $language_id);
				case RESULT_ACTIVATE_SUCCESS:
					return Internationalization::translate(STRING_ACCOUNT_ACTIVATED, $language_id);
				case ERROR_LOGIN_GENERIC:
					return Internationalization::translate(STRING_LOGIN_ERROR, $language_id);
				case RESULT_MFA_ENABLED:
					return Internationalization::translate(STRING_MFA_ENABLED, $language_id);
				case RESULT_BFP_MFA_CONFIRM:
					return Internationalization::translate(STRING_ENTER_MFA, $language_id);
				case ERROR_NULL_CONTEXT:
					return Internationalization::chainTranslate($language_id . STRING_NULL_X, STRING_CONTEXT);
				case RESULT_MFA_DISABLED:
					return Internationalization::chainTranslate($language_id, STRING_X_DISABLED, STRING_MFA);
				case ERROR_MFA_CONFIRM:
					return Internationalization::translate(STRING_INVALID_VERIFICATION_CODE, $language_id);
				case RESULT_MFA_GENERATED:
					return Internationalization::translate(STRING_MFA_SEED_GENERATED, $language_id);
				case RESULT_MFA_DISPLAY:
					return Internationalization::translate(STRING_DISPLAYING_MFA_QR, $language_id);
				case ERROR_CHANGEMAIL_EMPTY:
					return Internationalization::translate(STRING_EMAIL_CONFIRMATION_EMPTY, $language_id);
				case ERROR_PASSWORD_LOGGED:
					return Internationalization::translate(STRING_INCORRECT_PASSWORD, $language_id);
				case STATUS_DELETED:
					return Internationalization::translate(STRING_PAGE_UNAVAILABLE, $language_id);
				case ERROR_TEMPLATE_TABLE:
					return Internationalization::translate(STRING_TEMPLATE_TABLE_NO_EXISTE, $language_id);
				case ERROR_CIRCULAR_INHERITANCE:
					return Internationalization::translate(STRING_CIRCULAR_INHERITANCE, $language_id);
				case RESULT_BFP_IP_LOCKOUT_CONTINUED:
					return Internationalization::translate(STRING_IP_BLOCKED_FAILED_LOGINS);
				case WARNING_REFILL_SUCCESS_EXPIRED:
					return Internationalization::translate(STRING_PURCHASE_SUCCESSFUL_SESSION_TIMEOUT, $language_id);
				case RESULT_BFP_REGISTRATION_LOCKOUT:
					return Internationalization::translate(STRING_REGISTRATION_IP_FILTERED, $language_id);
				case ERROR_NULL_TOKEN:
					return Internationalization::translate(STRING_NULL_TOKEN, $language_id);
				case RESULT_RESENT_ACTIVATION:
					return Internationalization::translate(STRING_RESENT_ACTIVATION, $language_id);
				case RESULT_ORDER_SUCCESS:
					return Internationalization::chainTranslate($language_id, STRING_X_SUCCESSFUL, STRING_ORDER);
				case INFO_LOGIN_TO_ACTIVATE:
					return Internationalization::translate(STRING_LOGIN_TO_ACTIVATE, $language_id);
				case ERROR_NULL_MSGCLEARTEXT:
					return Internationalization::translate(STRING_MESSAGE_DECRYPTION_FAILED, $language_id);
				case STATUS_NO_NEWMSG:
					return Internationalization::translate(STRING_NO_NEW_MESSAGES, $language_id);
				case ERROR_NULL_MESSAGE_ID:
					return Internationalization::translate(STRING_LAST_MESSAGE_UNDEFINED, $language_id);
				case ERROR_NULL_QUERY:
					return Internationalization::translate(STRING_NULL_DATABASE_QUERY, $language_id);
				case ERROR_NULL_PARENT_TYPE:
					return Internationalization::translate(STRING_PARENT_DATATYPE_UNDEFINED, $language_id);
				case ERROR_NULL_TIMESTAMP:
					return Internationalization::chainTranslate($language_id, STRING_X_IS_UNDEFINED, STRING_TIMESTAMP);
				case STATUS_NEWMSG:
					return Internationalization::chainTranslate($language_id, STRING_NEW_X, STRING_MESSAGE);
				case ERROR_SODIUM_KEYSIZE:
					return Internationalization::chainTranslate($language_id, STRING_X_ERROR, STRING_SODIUM_KEYSIZE);
				case ERROR_NULL_PRODUCT_KEY:
					return Internationalization::chainTranslate($language_id, STRING_UNDEFINED_X, STRING_X_ID, STRING_PRODUCT);
				case ERROR_NULL_PRODUCT:
					return Internationalization::translate(STRING_NULL_PRODUCT_OBJECT, $language_id);
				case ERROR_NULL_USER_OBJECT:
					return Internationalization::translate(STRING_ERROR_RETRIEVING_USER, $language_id);
				case ERROR_ADMIN_CREDENTIALS:
					return Internationalization::translate(STRING_NICE_TRY, $language_id);
				case ERROR_CHILD_STATE:
					return Internationalization::translate(STRING_ILLEGAL_CHILD_LOAD_STATE, $language_id);
				case ERROR_NULL_PARENTCLASS:
					return Internationalization::chainTranslate($language_id, STRING_X_IS_UNDEFINED, STRING_PARENT_X, STRING_CLASS);
				case ERROR_IDENTICAL_KEY:
					return Internationalization::chainTranslate($language_id, STRING_X_FAILED, STRING_X_UPDATE, STRING_UNIQUE_X, STRING_KEY);
				case STATUS_UNINITIALIZED:
					Debug::error("{$f} unintialized data");
					ErrorMessage::deprecated($f);
					return Internationalization::chainTranslate($language_id, STRING_UNINITIALIZED_X, STRING_DATA);
				case ERROR_NULL_CORRESPONDENT_OBJECT:
					return Internationalization::chainTranslate($language_id, STRING_X_IS_UNDEFINED, STRING_X_OBJECT, STRING_CORRESPONDENT);
				case ERROR_IMEI_INVALID:
					return Internationalization::chainTranslate($language_id, STRING_INVALID_X, STRING_IMEI);
				case ERROR_IMEI_NOCHANGE:
					return Internationalization::chainTranslate($language_id, STRING_X_HAS_NOT_CHANGED, STRING_IMEI);
				case ERROR_NOTE_UNCHANGED:
					return Internationalization::translate(STRING_NOTHING_HERE, $language_id);
				case ERROR_NULL_STRING:
					return Internationalization::translate(STRING_EMPTY, $language_id);
				case RESULT_NOTE_UPDATED:
					return Internationalization::chainTranslate($language_id, STRING_X_UPDATED, STRING_NOTE);
				case ERROR_NULL_PUBLIC_SIGNATURE_KEY:
					return Internationalization::translate(STRING_SIGNATURE_PUBLIC_KEY_UNDEFINED, $language_id);
				case ERROR_ALREADY_DELETED:
					return Internationalization::translate(STRING_OBJECT_ALREADY_DELETED, $language_id);
				case ERROR_NULL_IDENTIFIER:
					return Internationalization::translate(STRING_NULL_IDENTIFIER, $language_id);
				case ERROR_NOT_EXPANDED:
					return Internationalization::chainTranslate($language_id, STRING_X_FAILED, STRING_X_EXPANSION, STRING_NODE);
				case ERROR_NULL_ORDER_KEY:
					return Internationalization::translate(STRING_ORDER_KEY_UNDEFINED, $language_id);
				case ERROR_ADMIN_ONLY:
					return Internationalization::translate(STRING_EMPLOYEES_ONLY, $language_id);
				case RESULT_NEWS_DELETED:
					return Internationalization::chainTranslate($language_id, STRING_X_DELETED, STRING_ARTICLE);
				case ERROR_PAYPAL_UNDEFINED:
					return Internationalization::chainTranslate($language_id, STRING_X_ERROR, STRING_X_API, STRING_PAYPAL);
				case ERROR_PAYPAL_PENDING_UNILATERAL:
					return "ERROR_PAYPAL_PENDING_UNILATERAL";
				case RESULT_BFP_WAIVER_SUCCESS:
					return Internationalization::translate(STRING_YOU_MAY_LOGIN, $language_id);
				case RESULT_BFP_IP_LOCKOUT_START:
					return Internationalization::translate(STRING_IP_BANNED_FAILED_LOGINS, $language_id);
				case ERROR_NULL_SIGNATURE:
					return Internationalization::chainTranslate($language_id, STRING_X_FAILED, STRING_X_VERIFICATION, STRING_DIGITAL_X, STRING_SIGNATURE);
				case ERROR_CANNOT_DISMISS:
					return Internationalization::translate(STRING_CANNOT_DISMISS_NOTIFICATION, $language_id);
				case ERROR_NOTIFICATION_TYPE:
					return Internationalization::chainTranslate($language_id, STRING_INVALID_X, STRING_X_TYPE, STRING_NOTIFICATION);
				case ERROR_ACCOUNT_DISABLED:
					return Internationalization::translate(STRING_ACCOUNT_DISABLED, $language_id);
				case STATUS_READY_WRITE:
					return "THIS IS NOT AN ERROR, DATA STRUCTURE IS READY TO WRITE";
				case ERROR_FILE_SIZE:
					return Internationalization::translate(STRING_FILE_TOO_BIG, $language_id);
				case ERROR_IMPOSSIBLE_VALUE:
					return Internationalization::translate(STRING_IMPOSSIBLE_VALUE, $language_id);
				case ERROR_UPLOAD_NO_FILE:
					return Internationalization::chainTranslate($language_id, STRING_X_FAILED, STRING_X_UPLOAD, STRING_FILE);
				case ERROR_MIME_TYPE:
					return Internationalization::translate(STRING_ILLEGAL_MIME_TYPE, $language_id);
				case ERROR_FILE_PARAMETERS:
					return Internationalization::chainTranslate($language_id, STRING_INVALID_X, STRING_FILE_PARAMETERS);
				case ERROR_NULL_CORRESPONDENT_KEY:
					return Internationalization::translate(STRING_NULL_CORRESPONDENT_KEY, $language_id);
				case ERROR_REGISTER_EMAIL_USED:
					return Internationalization::translate(STRING_EMAIL_UNAVAILABLE, $language_id);
				case ERROR_REQUIRED_ONLOAD:
					return Internationalization::translate(STRING_CRITICAL_DATA_UNLOADED, $language_id);
				case ERROR_NULL_PARENT:
					return Internationalization::chainTranslate($language_id, STRING_UNDEFINED_X, STRING_PARENT_X, STRING_OBJECT);
				case ERROR_SENDMAIL:
					return Internationalization::chainTranslate($language_id, STRING_X_ERROR, STRING_SENDMAIL);
				case ERROR_CONFIRMATION_CODE_UNDEFINED:
					return Internationalization::chainTranslate($language_id, STRING_UNDEFINED_X, STRING_X_CODE, STRING_CONFIRMATION);
				case ERROR_SIGNATURE_FAILED:
					return Internationalization::chainTranslate($language_id, STRING_X_FAILED, STRING_X_VERIFICATION, STRING_SIGNATURE);
				case ERROR_ALREADY_WAIVED:
					return Internationalization::translate(STRING_ALREADY_WAIVED, $language_id);
				case ERROR_CONFIRMATION_CODE_USED:
					return Internationalization::translate(STRING_CONFIRMATION_CODE_USED, $language_id);
				case ERROR_IPv6_UNSUPPORTED:
					return Internationalization::translate(STRING_IPV6_UNSUPPORTED, $language_id);
				case ERROR_NULL_XSRF_TOKEN:
					return Internationalization::translate(STRING_NULL_XSRF_TOKEN, $language_id);
				case ERROR_BLOCKED_IP_ADDRESS:
					return Internationalization::translate(STRING_YOUR_IP_BANNED, $language_id);
				case ERROR_FILTER_LOCKED_OUT:
					return Internationalization::translate(STRING_CANNOT_LOCKOUT_SELF, $language_id);
				case RESULT_CIDR_UNMATCHED:
					return Internationalization::chainTranslate($language_id, STRING_UNAUTHORIZED_X, STRING_IP_ADDRESS);
				case RESULT_IP_AUTHORIZED:
					return Internationalization::chainTranslate($language_id, STRING_X_AUTHORIZED, STRING_IP_ADDRESS);
				case ERROR_NULL_REQUEST_ATTEMPT:
					return Internationalization::translate(STRING_LOG_UNDEFINED, $language_id);
				// case RESULT_BFP_WHITELIST_UNAUTHORIZED:
				// return Internationalization::translate(STRING_UNAUTHORIZED_IP, $language_id);
				case ERROR_ARRAY_ENCODING:
					return Internationalization::translate(STRING_INVALID_ARRAY_ENCODING, $language_id);
				case ERROR_SERVER_ALREADY_INITIALIZED:
					return Internationalization::translate(STRING_SERVER_ALREADY_INITIALIZED, $language_id);
				case ERROR_PLZLOGIN_MESSENGER:
					return Internationalization::translate(STRING_PLZLOGIN_MESSENGER, $language_id);
				case ERROR_NULL_USECASE:
					return Internationalization::chainTranslate($language_id, STRING_X_UNDEFINED, STRING_USE_CASE);
				case ERROR_ADMIN_FORBIDDEN:
					return Internationalization::translate(STRING_ADMIN_FORBIDDEN, $language_id);
				case ERROR_DEPRECATED:
					return Internationalization::translate(STRING_FEATURE_DEPRECATED, $language_id);
				case ERROR_PASSWORDS_UNDEFINED:
					return Internationalization::translate(STRING_BOTH_PASSWORDS_UNDEFINED, $language_id);
				case ERROR_NULL_PASSWORD_HASH:
					return Internationalization::chainTranslate($language_id, STRING_NULL_X, STRING_PASSWORD_HASH);
				case STATUS_NOTHING_SUBMAT:
					return Internationalization::translate(STRING_NOTHING_HERE_YET, $language_id);
				case ERROR_NULL_USER_KEY:
					return Internationalization::chainTranslate($language_id, STRING_X_UNDEFINED, STRING_X_KEY, STRING_CLIENT);
				case ERROR_INVOICE_FORM_EMPTY:
					return Internationalization::translate(STRING_ENTER_BILLING_INFO, $language_id);
				case RESULT_BFP_RETRY_LOGIN:
					return Internationalization::translate(STRING_TRY_AGAIN, $language_id);
				case ERROR_NULL_SIGNATORY:
					return Internationalization::chainTranslate($language_id, STRING_UNDEFINED_X, STRING_SIGNATORY);
				case ERROR_NULL_TARGET_OBJECT:
					return Internationalization::chainTranslate($language_id, STRING_NULL_X, STRING_X_OBJECT, STRING_TARGET);
				case ERROR_ACCOUNT_DISABLED:
					return Internationalization::translate(STRING_ACCOUNT_DISABLED, $language_id);
				case ERROR_MESSAGE_FLOOD:
					return Internationalization::translate(STRING_SUBMISSION_REJECTED_FLOOD, $language_id);
				case ERROR_MESSAGE_ACCESS:
					return Internationalization::translate(STRING_MESSENGER_PRIVILEGES_REVOKED, $language_id);
				case ERROR_INTEGER_OOB:
					return Internationalization::translate(STRING_INTEGER_OOB, $language_id);
				case STATUS_INSERT_SUCCESSFUL:
					return Internationalization::chainTranslate($language_id, STRING_X_ACCEPTED, STRING_SUBMISSION);
				case RESULT_EDIT_COMMENT_SUCCESS:
					return Internationalization::chainTranslate($language_id, STRING_X_SUCCESSFULLY, STRING_X_UPDATED, STRING_COMMENT);
				case ERROR_NULL_OBJECTNUM:
					return static::chainTranslate($language_id, STRING_X_UNDEFINED, STRING_X_NUMBER, STRING_OBJECT);
				case ERROR_EXPIRED_PUSH_SUBSCRIPTION:
					return Internationalization::chainTranslate($language_id, STRING_EXPIRED_X, STRING_PUSH_SUBSCRIPTION);
				case ERROR_NULL_PICKUP_LOCATION_KEY:
					return Internationalization::translate(STRING_PICKUP_LOCATION_UNDEFINED, $language_id);
				case ERROR_NULL_DELIVERY_LOCATION_KEY:
					return Internationalization::translate(STRING_DELIVERY_LOCATION_UNDEFINED, $language_id);
				case ERROR_NO_ADDRESSES:
					return Internationalization::translate(STRING_NO_ADDRESSES, $language_id);
				case ERROR_ATTACHMENTS_DISABLED:
					return Internationalization::translate(STRING_ATTACHMENTS_DISABLED, $language_id);
				case RESULT_LANGUAGE_SETTINGS_UPDATED:
					return Internationalization::chainTranslate($language_id, STRING_X_UPDATED, STRING_X_SETTINGS, STRING_LANGUAGE);
				case RESULT_CHANGEMAIL_SUBMIT:
					$post = getInputParameters();
					return Internationalization::translate(STRING_CHANGEMAIL_SUBMIT, $language_id, $post['emailAddress']);
				case ERROR_ACTIVATE_ALREADY_LOGGED_IN:
					$full = new FullAuthenticationData();
					$name = $full->getName();
					return Internationalization::translate(STRING_ACTIVATION_PROHIBITED_ALREADY_LOGGED_IN, $language_id, $name);
				case RESULT_ALREADY_CREDITED:
					return Internationalization::translate(STRING_ALREADY_CREDITED, $language_id);
				case ERROR_USER_NOT_FOUND_POST_KEY:
					$post = getInputParameters();
					return Internationalization::translate(STRING_USER_NOT_FOUND_POST_KEY, $language_id, $post['uniqueKey']);
				case RESULT_EDITUSER_SUCCESS_POSTKEY:
					$post = getInputParameters();
					$settings = Internationalization::translate(STRING_X_FOR_Y, $language_id, Internationalization::translate(STRING_SETTINGS), $post['name']);
					return Internationalization::chainTranslate($language_id, STRING_SUCCESSFULLY_X, STRING_UPDATED_X, $settings);
				case RESULT_REFILL_SUCCESS_SESSION_QUANTITY:
					return Internationalization::translate(STRING_REFILL_SUCCESS_SESSION_QUANTITY, $language_id);
				case RESULT_TEMPLATE_UPDATE:
					$post = getInputParameters();
					return Internationalization::translate(STRING_TEMPLATE_UPDATED, $language_id, htmlspecialchars($post['name']));
				case RESULT_TEMPLATE_INSERT:
					$post = getInputParameters();
					return Internationalization::translate(STRING_TEMPLATE_INSERTED, $language_id, htmlspecialchars($post['name']));
				case ERROR_TEMPLATE_NOT_FOUND:
					return Internationalization::chainTranslate($language_id, STRING_X_NOT_FOUND, STRING_TEMPLATE);
				case ERROR_INVALID_CURRENCY:
					return Internationalization::chainTranslate($language_id, STRING_INVALID_X, STRING_CURRENCY);
				case ERROR_USER_ROLE:
					return Internationalization::chainTranslate($language_id, STRING_INVALID_X, STRING_USER_X, STRING_ROLE);
				case ERROR_LOCATION_KEY:
					return Internationalization::chainTranslate($language_id, STRING_INVALID_X, STRING_X_KEY, STRING_LOCATION);
				case ERROR_APPOINTMENT_TYPE:
					return Internationalization::chainTranslate($language_id, STRING_INVALID_X, STRING_X_TYPE, STRING_APPOINTMENT);
				case RESULT_CHECKOUT_SUCCESS:
					return Internationalization::chainTranslate($language_id, STRING_X_SUCCESSFUL, STRING_PURCHASE);
				case ERROR_IDENTICAL_PARENTKEY:
					return Internationalization::chainTranslate($language_id, STRING_X_IDENTICAL_TO_OLD_ONE, STRING_NEW_X, STRING_X_KEY, STRING_PARENT);
				case ERROR_HCAPTCHA:
					return Internationalization::chainTranslate($language_id, STRING_X_FAILED, STRING_X_VERIFICATION, STRING_HCAPTCHA);
				case RESULT_MANUAL_PAYMENT_SUBMITTED:
					return Internationalization::translate(STRING_MANUAL_PAYMENT_SUBMITTED, $language_id);
				case ERROR_INVALID_USERNAME:
					return Internationalization::chainTranslate($language_id, STRING_INVALID_X, STRING_USERNAME);
				case ERROR_REJECTION_REASON_REQUIRED:
					$reason = Internationalization::chainTranslate($language_id, STRING_REASON_FOR_X, STRING_REJECTION);
					$required = Internationalization::translate(STRING_REQUIRED, $language_id);
					return Internationalization::translate(STRING_X_IS_Y, $language_id, $reason, $required);
				case STATUS_UNCHANGED:
					return Internationalization::translate(STRING_NOTHING_TO_REPORT);
				case ERROR_INVALID_IP_ADDRESS:
					return Internationalization::chainTranslate($language_id, STRING_INVALID_X, STRING_IP_ADDRESS);
				case STATUS_DISPLAY_PROMPT:
					return "You should be seeing a prompt instead of this error message";
				case RESULT_IP_BANNED:
					return Internationalization::chainTranslate($language_id, STRING_X_BANNED, STRING_IP_ADDRESS);
				case RESULT_DELETE_SESSIONS_SUCCESS:
					return Internationalization::chainTranslate($language_id, STRING_SUCCESSFULLY_X, STRING_DELETED_X, STRING_SAVED_X, STRING_SESSIONS);
				case ERROR_INVALID_MFA_OTP:
					return Internationalization::chainTranslate($language_id, STRING_INVALID_X, STRING_MFA_OTP);
				case ERROR_NULL_MFA_SEED:
					return Internationalization::chainTranslate($language_id, STRING_NULL_X, STRING_MFA_SEED);
				case ERROR_INVALID_PASSWORD:
					return Internationalization::chainTranslate($language_id, STRING_INVALID_X, STRING_PASSWORD);
				case ERROR_INVALID_EMAIL_ADDRESS:
					return Internationalization::chainTranslate($language_id, STRING_INVALID_X, STRING_EMAIL_ADDRESS);
				case STATUS_PRELAZYLOAD:
					return "Lazy loading failed";
				case ERROR_ANONYMOUS_REQUIRED:
					return Internationalization::chainTranslate($language_id, STRING_X_ONLY, STRING_X_USERS, STRING_GUEST);
				case ERROR_0_SEARCH_RESULTS:
					return Internationalization::chainTranslate(null, STRING_NO_X, STRING_RESULTS);
				case RESULT_BFP_WHITELIST_UNAUTHORIZED:
				case ERROR_IP_ADDRESS_NOT_AUTHORIZED:
					return Internationalization::translate(STRING_X_IS_Y, null, Internationalization::translate(STRING_YOUR_X, null, Internationalization::translate(STRING_IP_ADDRESS)), Internationalization::translate(STRING_UNAUTHORIZED));
				case ERROR_KEY_COLLISION:
					return Internationalization::chainTranslate($language_id, STRING_X_ERROR, STRING_X_COLLISION, STRING_KEY);
				case ERROR_FORBIDDEN:
					return Internationalization::translate(STRING_FORBIDDEN);
				case RESULT_BFP_USERNAME_LOCKOUT_START:
					return "Your account has been locked";
				case ERROR_FILE_NOT_FOUND:
					return "File not found";*/
				default:
					return substitute(_("Error code %1%"), $status);
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}

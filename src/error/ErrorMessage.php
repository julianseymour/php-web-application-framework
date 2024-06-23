<?php
namespace JulianSeymour\PHPWebApplicationFramework\error;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\login\FullAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\language\Internationalization;
use JulianSeymour\PHPWebApplicationFramework\language\settings\LanguageSettingsData;
use Exception;

abstract class ErrorMessage{

	public static function getVisualNotice($t){
		$div = new DivElement();
		$div->addClassAttribute("visual_result_container", "background_color_2");
		// $div->addClassAttribute("shadow");
		$div->setInnerHTML($t);
		return $div;
	}

	public static function getVisualError($status){
		return static::getVisualNotice(ErrorMessage::getResultMessage($status));
	}

	public static function getInfoBoxStatus($status){
		return static::getInfoBoxElement(static::getVisualError($status));
	}

	/**
	 * shortcut fatal error for unimplemented functions
	 *
	 * @param string $f
	 */
	public static function unimplemented($f){
		Debug::error("{$f}: " . ErrorMessage::getResultMessage(ERROR_NOT_IMPLEMENTED));
	}

	public static function deprecated($f){
		Debug::error("{$f}: " . ErrorMessage::getResultMessage(ERROR_DEPRECATED));
	}

	public static function getResultMessage($status){
		$f = __METHOD__;
		try{
			switch($status){
				case (ERROR_MYSQL_QUERY):
					return _("MySQL query error.");
				case (ERROR_MYSQL_RESULT):
					return _("MySQL fetch result error.");
				case (ERROR_XSRF):
				case ERROR_SESSION_EXPIRED:
					return _("Session expired.");
				case (ERROR_MYSQL_EXECUTE):
					return _("MySQL execution error.");
				case (ERROR_MYSQL_BIND):
					return _("MySQL bind parameters error.");
				case (ERROR_MYSQL_PREPARE):
					return _("MySQL prepared query statement error.");
				case ERROR_MYSQL_CONNECT:
					return _("MySQL connection error.");
				case (ERROR_INTERNAL):
				case ERROR_HONEYPOT:
					return _("Internal server error.");
				case ERROR_LOGIN_CREDENTIALS:
					return _("Invalid credentials.");
				case (ERROR_ALREADY_LOGGED):
					return _("You are already logged in.");
				case (RESULT_LOGGED_OUT):
					return _("You have been logged out.");
				case (SUCCESS):
					return _("Your request was processed successfully.");
				case (RESULT_RESET_SUBMIT):
					return _("An email has been sent to the address we have on file containing instructions on how to reset your password. If you do not receive an email check your spam folder. If the problem persists please contact us directly.");
				case RESULT_CHANGEPASS_SUCCESS:
					return _("Your password has been updated.");
				case ERROR_IP_ADDRESS_BLOCKED_BY_USER:
					return _("IP address blocked by user.");
				case ERROR_NOT_IMPLEMENTED:
					return _("This feature has not been implemented.");
				case (ERROR_LINK_EXPIRED):
					return _("The link you followed has expired.");
				case ERROR_CHECKOUT_IMEI_TAMPER:
				case ERROR_CHECKOUT_MODE:
					return _("IMEI mode undefined.");
				case ERROR_CHECKOUT_AUTO_REJECT_ACCESS:
					return _("Unauthorized transaction.");
				case RESULT_CHECKOUT_CONFIRM:
					return _("Confirm transaction:");
				case ERROR_BASE_SVCKEY:
					return _("Undefined base service key.");
				case ERROR_DISPATCH_NOTHING:
					return _("Nothing to report.");
				case ERROR_KEY_UNDEFINED:
					return _("Unidefined unique identifier.");
				case ERROR_MUST_LOGIN:
					return _("You must login to access this feature.");
				case ERROR_IDENTICAL_NAME:
					return _("Identical name.");
				case ERROR_NULL_PARENT_KEY:
					return _("Undefined parent key.");
				case ERROR_NOT_FOUND:
				case ERROR_FILE_NOT_FOUND:
					return _("File not found.");
				case ERROR_NOT_MAPPED:
					return _("Unregistered unique key.");
				case ERROR_NAME_LENGTH:
					return _("Invalid username length.");
				case ERROR_PASSWORD_WEAK:
					return _("Invalid password.");
				case ERROR_REGISTER_NULL_CONFIRM:
					return _("Password confirmation required.");
				case ERROR_PASSWORD_UNDEFINED:
					return _("Undefined password.");
				case ERROR_CHANGEMAIL_BADCODE:
					return _("Confirmation code validation failed.");
				case ERROR_PASSWORD_MISMATCH:
					return _("Passwords do not match.");
				case ERROR_NAME_UNDEFINED:
					return _("Undefined name.");
				case ERROR_CHECKOUT_REPEAT:
					return _("Transaction rejected because it is a repeat purchase.");
				case ERROR_CHECKOUT_IDK_QTY:
					return _("You must specify a quantity to place this order.");
				case ERROR_CHECKOUT_EMPTY:
					return _("You must select at least one item to place an order.");
				case ERROR_TAMPER_POST:
					return _("Transaction blocked because the user tampered with POST.");
				case ERROR_PRICE_0CREDITS:
					return _("Invalid item price.");
				case ERROR_NULL_ITERATOR:
					return _("Iterator is undefined.");
				case ERROR_NULL_TEMPLATE:
					return _("Your account is not allowed to access this feature pending review by an administrator.");
				case ERROR_CHECKOUT_IMEI_SINGLE:
				case ERROR_IMEI_REQUIRED:
					return _("IMEI is required for this purchase.");
				case STATUS_DELETED:
					return _("Deletion successful.");
				case ERROR_NULL_SERVICE:
					return _("Undefined service.");
				case ERROR_NULL_IMPLICIT_SVC:
					return _("Undefined implicit service.");
				case ERROR_DUPLICATE_ENTRY:
					return _("Duplicate entry.");
				case ERROR_KEY_INHERITED:
					return _("Cycle detected.");
				case ERROR_TRANSACTION_STATE:
					return _("Error updating transaction state.");
				case RESULT_TRANSACTION_UPDATED:
					return _("Transaction update successful.");
				case ERROR_DEADBEAT:
					return _("Insufficient credit balance.");
				case RESULT_SETTINGS_UPDATED:
					return _("Your settings have been updated.");
				case RESULT_MFA_ENABLED:
					return _("Multifactor authentication is now enabled.");
				case RESULT_BFP_MFA_CONFIRM:
					return _("Enter the code from your authenticator app.");
				case RESULT_MFA_DISABLED:
					return _("Multifactor authentication is now disabled.");
				case ERROR_MFA_CONFIRM:
					return _("Invalid verification code.");
				case RESULT_MFA_GENERATED:
					return _("Generated a new MFA seed.");
				case RESULT_MFA_DISPLAY:
					return _("Displaying MFA QR code.");
				case RESULT_BFP_IP_LOCKOUT_CONTINUED:
					return _("Your IP address has been blocked due to excessive failed login attempts.");
				case WARNING_REFILL_SUCCESS_EXPIRED:
					return _("Your purchase was successful but your session timed out while you were authorizing payment. Please login again.");
				case RESULT_BFP_REGISTRATION_LOCKOUT:
					return _("Your IP address has already registered an account recently; your registration attempt has been filtered to protect the server from spam.");
				case ERROR_NULL_TOKEN:
					return _("The hidden input for preventing repeat submissions was not submitted; tampering with POST won't accomplish anything.");
				case RESULT_RESENT_ACTIVATION:
					return _("An email has been sent to your email address containing instructions on how to activate your account.");
				case RESULT_ORDER_SUCCESS:
					return _("Order placement successful.");
				case INFO_LOGIN_TO_ACTIVATE:
					return _("Login to activate your account.");
				case ERROR_NULL_MSGCLEARTEXT:
					return _("Decryption failed.");
				case STATUS_NO_NEWMSG:
					return _("No new messages.");
				case ERROR_NULL_MESSAGE_ID:
					return _("Undefined message ID.");
				case ERROR_NULL_QUERY:
					return _("Undefined query statement.");
				case ERROR_NULL_PARENT_TYPE:
					return _("Undefined parent type.");
				case ERROR_NULL_TIMESTAMP:
					return _("Timestamp is undefined.");
				case STATUS_NEWMSG:
					return _("New message");
				case ERROR_SODIUM_KEYSIZE:
					return _("Sodium keysize error.");
				case ERROR_NULL_PRODUCT_KEY:
					return _("Undefined product key.");
				case ERROR_NULL_PRODUCT:
					return _("Undefined product.");
				case ERROR_NULL_USER_OBJECT:
					return _("Error retrieving user data.");
				case ERROR_ADMIN_CREDENTIALS:
					return _("Nice try.");
				case ERROR_CHILD_STATE:
					return _("Illegal child load state.");
				case ERROR_KEY_IDENTICAL:
					return _("Error updating unique key.");
				case STATUS_UNINITIALIZED:
					Debug::error("{$f} unintialized data");
					ErrorMessage::deprecated($f);
					return _("Unitialized data.");
				case ERROR_NULL_CORRESPONDENT_OBJECT:
					return _("Undefined correspondent.");
				case ERROR_IMEI_INVALID:
					return _("Invalid IMEI.");
				case ERROR_IMEI_NOCHANGE:
					return _("IMEI has not changed.");
				case ERROR_NOTE_UNCHANGED:
					return _("Note unchanged.");
				case ERROR_NULL_STRING:
					return _("Empty string.");
				case RESULT_NOTE_UPDATED:
					return _("Note updated.");
				case ERROR_NULL_PUBLIC_SIGNATURE_KEY:
					return _("Public key is undefined.");
				case ERROR_ALREADY_DELETED:
					return _("This object has already been deleted.");
				case ERROR_NULL_IDENTIFIER:
					return _("Null identifier.");
				case ERROR_NOT_EXPANDED:
					return _("Node expansion failed.");
				case ERROR_NULL_ORDER_KEY:
					return _("Order key undefined.");
				case ERROR_EMPLOYEES_ONLY:
					return _("Employees only.");
				case RESULT_NEWS_DELETED:
					return _("Article deleted.");
				case ERROR_PAYPAL_UNDEFINED:
					return _("PayPal API error.");
				case ERROR_PAYPAL_PENDING_UNILATERAL:
					return "ERROR_PAYPAL_PENDING_UNILATERAL";
				case RESULT_BFP_WAIVER_SUCCESS:
					return _("You may now sign in.");
				case RESULT_BFP_IP_LOCKOUT_START:
					return _("Your IP address has been temporarily banned due to an excess of failed login attempts. Please try again in 10 minutes.");
				case ERROR_NULL_SIGNATURE:
					return _("Digital signature verification failed.");
				case ERROR_CANNOT_DISMISS:
					return _("You cannot dismiss this notification.");
				case ERROR_NOTIFICATION_TYPE:
					return _("Invalid notification type.");
				case ERROR_ACCOUNT_DISABLED:
					return _("Your account is disabled.");
				case STATUS_READY_WRITE:
					return "THIS IS NOT AN ERROR, DATA STRUCTURE IS READY TO WRITE";
				case ERROR_FILE_SIZE:
					return _("File size is too big.");
				case ERROR_IMPOSSIBLE_VALUE:
					return _("Impossible value.");
				case ERROR_UPLOAD_NO_FILE:
					return _("File upload failed.");
				case ERROR_MIME_TYPE:
					return _("Illegal MIME type.");
				case ERROR_FILE_PARAMETERS:
					return _("Invalid file parameters.");
				case ERROR_NULL_CORRESPONDENT_KEY:
					return _("Undefined correspondent key.");
				case ERROR_REGISTER_EMAIL_USED:
					return _("The email address you provided is unavailable for registration.");
				case ERROR_REQUIRED_ONLOAD:
					return _("Critical data was not loaded.");
				case ERROR_NULL_PARENT:
					return _("Undefined parent object.");
				case ERROR_SENDMAIL:
					return _("sendmail error.");
				case ERROR_CONFIRMATION_CODE_UNDEFINED:
					return _("Undefined confirmation code.");
				case ERROR_SIGNATURE_FAILED:
					return _("Signature verification failed.");
				case ERROR_ALREADY_WAIVED:
					return _("You already have a lockout waiver in effect for this IP address.");
				case ERROR_CONFIRMATION_CODE_USED:
					return _("The confirmation code you submitted has already been used.");
				case ERROR_IPv6_UNSUPPORTED:
					return _("IPv6 is unsupported.");
				case ERROR_NULL_XSRF_TOKEN:
					return _("Undefined anti-XSRF token.");
				case ERROR_BLOCKED_IP_ADDRESS:
					return _("Your IP address is blocked.");
				case ERROR_FILTER_LOCKED_OUT:
					return _("Failed to update firewall settings. You cannot lock yourself out of your account.");
				case RESULT_CIDR_UNMATCHED:
					return _("Your IP address is unauthorized.");
				case RESULT_IP_AUTHORIZED:
					return _("Your IP address is authorized.");
				case ERROR_NULL_REQUEST_ATTEMPT:
					return _("Access attempt is undefined.");
				case ERROR_ARRAY_ENCODING:
					return _("Invalid array encoding.");
				case ERROR_SERVER_ALREADY_INITIALIZED:
					return _("Server has already been initialized.");
				case ERROR_PLZLOGIN_MESSENGER:
					return _("Please log in to use the messenger.");
				case ERROR_NULL_USECASE:
					return _("Use case is undefined.");
				case ERROR_ADMIN_FORBIDDEN:
					return _("This feature is of no concern to administrators.");
				case ERROR_DEPRECATED:
					return _("This feature is deprecated.");
				case ERROR_PASSWORDS_UNDEFINED:
					return _("Both password fields were left empty.");
				case ERROR_NULL_PASSWORD_HASH:
					return _("Null password hash.");
				case STATUS_NOTHING_HERE_YET:
					return _("Nothing to see here.");
				case ERROR_NULL_USER_KEY:
					return _("Undefined user key.");
				case ERROR_INVOICE_FORM_EMPTY:
					return _("Enter billing information.");
				case RESULT_BFP_RETRY_LOGIN:
					return _("Try again.");
				case ERROR_NULL_SIGNATORY:
					return _("Undefined signatory.");
				case ERROR_NULL_TARGET_OBJECT:
					return _("Null target object.");
				case ERROR_ACCOUNT_DISABLED:
					return _("Your account is disabled.");
				case ERROR_MESSAGE_FLOOD:
					return _("Your submission has been rejected because you have been flooding the server with requests.");
				case ERROR_MESSAGE_ACCESS:
					return _("Your messenger privileges have been provoked.");
				case ERROR_INTEGER_OOB:
					return _("Integer out of bounds.");
				case RESULT_SUBMISSION_ACCEPTED:
					return _("Submission accepted.");
				case RESULT_EDIT_COMMENT_SUCCESS:
					return _("Successfully edited comment.");
				case ERROR_NULL_OBJECTNUM:
					return _("Undefined object number.");
				case ERROR_EXPIRED_PUSH_SUBSCRIPTION:
					return _("Push subscription is expired.");
				case ERROR_NULL_PICKUP_LOCATION_KEY:
				case ERROR_NULL_DELIVERY_LOCATION_KEY:
					return _("Undefined location key.");
				case ERROR_NO_ADDRESSES:
					return _("No addresses.");
				case ERROR_ATTACHMENTS_DISABLED:
					return _("Attachments disabled.");
				case RESULT_LANGUAGE_SETTINGS_UPDATED:
					return _("Language settings updated.");
				case RESULT_CHANGEMAIL_SUBMIT:
					$post = getInputParameters();
					return substitute(_("You must confirm must your new email address in order to finalize the changes to your account. Instructions on completing the process have been sent to %1%. If you cannot find the message in your inbox or spam folder please contact us directly."), $post['emailAddress']);
				case ERROR_ACTIVATE_ALREADY_LOGGED_IN:
					$full = new FullAuthenticationData();
					$name = $full->getName();
					return substitute(_("Activation prohibited: You are already logged in as %1%."), $name);
				case RESULT_ALREADY_CREDITED:
					$post = getInputParameters();
					return substitute(_("Your account has already been credited %1% credits."), $post['quantity']);
				case RESULT_EDITUSER_SUCCESS_POSTKEY:
					$post = getInputParameters();
					return substitute(_("Successfully updated settings for user %1%."), $post['name']);
				case ERROR_INVALID_CURRENCY:
					return _("Invalid currency.");
				case ERROR_USER_ROLE:
					return _("Invalid user role.");
				case ERROR_LOCATION_KEY:
					return _("Invalid location key.");
				case ERROR_APPOINTMENT_TYPE:
					return _("Invalid appointment type.");
				case ERROR_IDENTICAL_PARENTKEY:
					return _("New parent key is identical to the old one.");
				case ERROR_HCAPTCHA:
					return _("hCaptcha verification failed.");
				case RESULT_MANUAL_PAYMENT_SUBMITTED:
					return _("Submission accepted. You will be notified as soon as the administrator verifies payment.");
				case ERROR_INVALID_USERNAME:
					return _("Invalid username.");
				case ERROR_REJECTION_REASON_REQUIRED:
					return _("Rejection reason required.");
				case STATUS_UNCHANGED:
					return _("Nothing to report.");
				case ERROR_INVALID_IP_ADDRESS:
					return _("Invalid IP address.");
				case STATUS_DISPLAY_PROMPT:
					return "You should be seeing a prompt instead of this error message";
				case RESULT_IP_BANNED:
					return _("IP address banned.");
				case RESULT_DELETE_SESSIONS_SUCCESS:
					return _("Successfully deleted saved sessions.");
				case ERROR_INVALID_MFA_OTP:
					return _("Invalid MFA OTP.");
				case ERROR_NULL_MFA_SEED:
					return _("Undefined MFA seed.");
				case ERROR_INVALID_PASSWORD:
					return _("Invalid password.");
				case ERROR_INVALID_EMAIL_ADDRESS:
					return _("Invalid email address.");
				case STATUS_PRELAZYLOAD:
					return "Lazy loading failed";
				case ERROR_ANONYMOUS_REQUIRED:
					return _("Guest users only.");
				case ERROR_0_SEARCH_RESULTS:
					return _("No results.");
				case RESULT_BFP_WHITELIST_UNAUTHORIZED:
				case ERROR_IP_ADDRESS_NOT_AUTHORIZED:
					return _("Your IP address is unauthorized.");
				case ERROR_KEY_COLLISION:
					return _("Key collision error.");
				case ERROR_FORBIDDEN:
					return _("Forbidden");
				case RESULT_BFP_USERNAME_LOCKOUT_START:
					return _("Your account has been locked.");
				default:
					Debug::warning("{$f} this function is being deprecated");
					return substitute(_("Error code %1%."), $status);
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}
}

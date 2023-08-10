<?php
namespace JulianSeymour\PHPWebApplicationFramework\language;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use Exception;

class StringTable
{

	public static function getDefaultLanguageString($string_id)
	{
		$f = __METHOD__; //StringTable::class . "::getDefaultLanguageString({$string_id})";
		try {
			if ($string_id instanceof Element) {
				Debug::error("{$f} don't pass elements to this function");
			}
			switch ($string_id) {
				case 0:
					Debug::error("{$f} undefined string");
				case STRING_FIRST:
					return "First";
				case STRING_UNINITIALIZED:
					return "Uninitialized";
				case STRING_LOADED:
					return "Loaded";
				case STRING_EXPANDED:
					return "Expanded";
				case STRING_NONE:
					return "None";
				case STRING_APPENDING_INSERTED_NODE:
					return "Appending inserted node";
				case STRING_X_STATE:
					return "%1% state";
				case STRING_OPERATION:
					return "Operation";
				case STRING_NOT_LOGGED_IN:
					return "You are not logged in";
				case STRING_FAILED_DATABASE_QUERY:
					return "Failed mysql query";
				case STRING_FAILED_DATABASE_RESULT:
					return "Failed to get results of prepared query";
				case STRING_SESSION_EXPIRED:
					return "Session expired, please reload the page";
				case STRING_NOTHING_HERE:
					return "Nothing to see here";
				case STRING_FAILED_DATABASE_EXECUTE:
					return "Unable to execute SQL statement";
				case STRING_FAILED_DATABASE_BIND:
					return "Unable to bind parameters";
				case STRING_FAILED_DATABASE_PREPARE:
					return "Unable to prepare SQL query statement";
				case STRING_FAILED_DATABASE_CONNECT:
					return "Unable to connect to database";
				case STRING_CANNOT_PROCESS_REQUEST:
					return "Your request could not be processed due to an internal server error";
				case STRING_INPUT_BLANK:
					return "Your request was not processed due to critical input field(s) left blank";
				case STRING_INVALID_X:
					return "Invalid %1%";
				case STRING_ALREADY_ACTIVE:
					return "Your account has already been activated, but nice to see you again anyways.";
				case STRING_ACCOUNT_INACTIVE:
					return "You haven't activated your account. Please click the link in the email we sent to [email protected] and follow the instructions to finish activating your account";
				case STRING_ALREADY_LOGGED:
					return "You are already logged in!";
				case STRING_LOGGED_OUT:
					return "You have been logged out.";
				case STRING_REQUEST_PROCESSED:
					return "Request processed successfully. Please notify the webmaster that whatever you just did lacks a unique success message.";
				case STRING_RESET_SUBMIT:
					return "An email has been sent to the address we have on file containing instructions on how to reset your password. If you do not receive an email check your spam folder. If the problem persists please contact us directly.";
				case STRING_PASSWORD_RESET:
					return "Password reset";
				case STRING_PASSWORD_HAS_BEEN_RESET:
					return "Your password has been reset"; // . Your messages will be restored next time the administrator logs in.";
				case STRING_PASSWORD_UPDATED_PLEASE_REFRESH:
					return "Your password has been updated. Please refresh the page and sign in again.";
				case STRING_PASSWORD_OR_CONFIRMATION_MISSING:
					return "Password and/or confirmation missing";
				case STRING_SERVER_MAINTENANCE:
					return "Your request could not be completed because the server is currently undergoing maintenance";
				case STRING_EMAIL_UPDATED:
					return "Your email address has been updated.";
				case STRING_LINK_EXPIRED:
					return "The link you followed has expired";
				// case STRING_IMEI_MODE_UNDEFINED:
				// return "IMEI selection mode is undefined. If you have tampered with post please leave.";
				case STRING_X_UNAUTHORIZED:
					return "%1% unauthorized";
				case STRING_CONFIRM_X:
					return "Confirm %1%";
				case STRING_ABSTRACT_X:
					return "Abstract %1%";
				case STRING_NOTHING_TO_REPORT:
					return "Nothing to report";
				case STRING_X_IS_UNDEFINED:
					return "%1% is undefined";
				case STRING_MUST_LOGIN:
					return "You must be logged in to use this page.";
				case STRING_INVALID_X:
					return "Invalid %1%";
				case STRING_IDENTICAL_NAME:
					return "New unique identifier is identical to the old one";
				// case STRING_NULL_PARENT_KEY:
				// return "Parent key is null";
				case STRING_NULL_X:
					return "Null %1%";
				case STRING_KEY_UNMAPPED:
					return "Key is not mapped";
				case STRING_DATABASE_OPERATION:
					return "Database operation";
				case STRING_USERNAME_LENGTH_X:
					return "Usernames must be less than %1% characters long";
				case STRING_PASSWORD_WEAK_X_Y:
					return "Passwords must be between %1% and %2% characters.";
				case STRING_PASSWORD_CONFIRMATION_REQURED:
					return "Password confirmation is required";
				case STRING_PASSWORD_SUBMITTED_EMPTY:
					return "Password field was submitted empty";
				case STRING_CONFIRMATION_CODE_MISMATCH:
					return "The confirmation code you provided does not match the one we have on record";
				case STRING_PASSWORDS_MISMATCH:
					return "The passwords you submitted do not match";
				case STRING_X_UNDEFINED:
					return "%1% undefined";
				case STRING_TRANSACTION_REJECTED_REPEAT:
					return "Transaction was rejected because it is a repeat purchase";
				case STRING_MUST_PROVIDE_QUANTITY:
					return "You must provide the quantity for purchases where IMEI# is unknown";
				case STRING_MUST_PROVIDE_IMEI:
					return "You must provide an IMEI number";
				case STRING_IMEI_SUBMISSION_MODE:
					return "IMEI submission mode";
				case STRING_CHECKOUT_EMPTY:
					return "No services or service options posted";
				case STRING_TRANSACTION_BLOCKED_TAMPER_POST:
					return "Transaction has been blocked because the client tampered with post";
				case STRING_PRICE_0CREDITS:
					return "Services cannot be priced at 0 credits";
				case STRING_CHECKOUT_NULL_ITERATOR:
					return "We lost track of how many items you were purchasing, please try again";
				case STRING_ACCOUNT_PENDING_REVIEW:
					return "Your account is pending administrative review";
				case STRING_IMEI_FIELD_IS_MANDATORY:
					return "IMEI field is mandatory. If you do not know your device's IMEI click \"IMEI unknown\" under the section \"IMEI Info\".";
				case STRING_DELETION_SUCCESSFUL:
					return "Deletion successful";
				case STRING_NULL_SERVICE:
					return "Received a null service object";
				// case STRING_IMPLICIT_SERVICE_UNDEFINED:
				// return "Implicit service wrapper object undefined";
				case STRING_DUPLICATE_ENTRY:
					return "Duplicate entry found";
				case STRING_PARENT_CHILD_SAME_KEY:
					return "Parent and child have the same unique key";
				case STRING_ERROR_UPDATING_TRANSACTION_STATE:
					return "There was an error updating the state of your transaction";
				case STRING_TRANSACTION_UPDATE_SUCCESSFUL:
					return "Successfully updated transaction status";
				case STRING_INSUFFICIENT_BALANCE:
					return "Insufficient balance."; // <label for=\"credit_menu_check\">Click here to purchase credits.</label>";
				case STRING_SETTINGS_UPDATED:
					return "Your settings have been updated";
				case STRING_ACCOUNT_ACTIVATED:
					return "Your account has been activated";
				case STRING_LOGIN_ERROR:
					return "Login error";
				case STRING_MFA_ENABLED:
					return "Multifactor authentication enabled. Don't lose your device or you'll be locked out forever.";
				case STRING_ENTER_MFA:
					return "Enter 6 digit temporary multifactor authentication code";
				case STRING_MFA_SEED_GENERATED:
					return "Generated a multifactor authentication seed. Scan the QR code with your authentication app.";
				case STRING_DISPLAYING_MFA_QR:
					return "Displaying MFA QR code";
				// case STRING_INVALID_MFA_OTP:
				// return "Invalid multifactor authentication one-time password";
				case STRING_EMAIL_CONFIRMATION_EMPTY:
					return "email and/or confirmation field is empty";
				case STRING_INCORRECT_PASSWORD:
					return "Incorrect password"; // for a user already logged in
				case STRING_PAGE_UNAVAILABLE:
					return "The page you are attempting to access is no longer available";
				case STRING_TEMPLATE_TABLE_NO_EXISTE:
					return "Template table does not exist";
				case STRING_CIRCULAR_INHERITANCE:
					return "Circular inheritance";
				case STRING_IP_BLOCKED_FAILED_LOGINS:
					return "Your IP address has been blocked due to excessive failed login attempts";
				case STRING_PURCHASE_SUCCESSFUL_SESSION_TIMEOUT:
					return "Your purchase was successful but your session timed out while you were authorizing payment. Please login again.";
				case STRING_REGISTRATION_IP_FILTERED:
					return "Your IP address has already registered an account recently; your registration attempt has been filtered to protect the server from spam";
				case STRING_NULL_TOKEN:
					return "The hidden input for preventing repeat submissions was not submitted; tampering with POST won't accomplish anything";
				case STRING_RESENT_X:
					return "Resent %1%";
				case STRING_X_ACTIVATION:
					return "%1% activation";
				case STRING_LOGIN_TO_ACTIVATE:
					return "Log in to activate your account";
				case STRING_MESSAGE_DECRYPTION_FAILED:
					return "Message decryption failed";
				case STRING_NO_NEW_MESSAGES:
					return "No new messages";
				case STRING_LAST_LOADED_MESSAGE_ID:
					return "Last loaded message ID";
				case STRING_DATABASE_QUERY:
					return "MYSQL query";
				case STRING_PARENT_X:
					return "Parent %1%";
				case STRING_DATATYPE:
					return "Datatype";
				case STRING_TIMESTAMP:
					return "Timestamp";
				case STRING_NEW_X:
					return "New %1%";
				case STRING_SODIUM_KEYSIZE:
					return "Sodium keysize";
				case STRING_X_ID:
					return "%1% ID";
				// case STRING_NULL_PRODUCT_OBJECT:
				// return "Undefined product reference";
				case STRING_ERROR_RETRIEVING_USER:
					return "Error retrieving user data";
				case STRING_NICE_TRY:
					return "Nice try";
				// case STRING_ILLEGAL_CHILD_LOAD_STATE:
				// return "Illegal child objects array load state";
				case STRING_X_CLASS:
					return "%1% class";
				case STRING_X_UPDATE:
					return "%1% update";
				case STRING_UNINITIALIZED_X:
					return "Uninitialized %1%";
				case STRING_DATA:
					return "Data";
				case STRING_X_OBJECT:
					return "%1% object";
				case STRING_INVALID_X:
					return "Invalid %1%";
				case STRING_X_HAS_NOT_CHANGED:
					return "%1% has not changed";
				case STRING_NOTHING_HERE:
					return "Nothing to see here";
				case STRING_EMPTY:
					return "Empty string";
				// case STRING_NOTE_UPDATED:
				// return "Note updated";
				// case STRING_SIGNATURE_PUBLIC_KEY:
				// return "Public signture key";
				case STRING_OBJECT_ALREADY_DELETED:
					return "Object was already deleted";
				case STRING_NULL_IDENTIFIER:
					return "Object has no identifier";
				case STRING_X_EXPANSION:
					return "%1% expansion";
				case STRING_X_KEY:
					return "%1% key";
				case STRING_EMPLOYEES_ONLY:
					return "Employees only";
				case STRING_ARTICLE:
					return "Article";
				case STRING_X_API:
					return "%1% API";
				case STRING_YOU_MAY_LOGIN:
					return "You many now attempt to login again";
				case STRING_IP_BANNED_FAILED_LOGINS:
					return "Your IP address has been temporarily banned due to an excess of failed login attempts. Please try again in 10 minutes.";
				case STRING_CANNOT_DISMISS_NOTIFICATION:
					return "That notification cannot be dismissed until you have addressed the issue requiring your attention";
				case STRING_X_TYPE:
					return "%1% type";
				case STRING_ACCOUNT_DISABLED:
					return "Your account is disabled";
				case STRING_FILE_TOO_BIG:
					return "The file you uploaded is too big";
				case STRING_IMPOSSIBLE_VALUE:
					return "Impossible value";
				case STRING_X_UPLOAD:
					return "%1% upload";
				case STRING_ILLEGAL_X:
					return "Illegal %1%";
				case STRING_MIME:
					return "MIME";
				case STRING_X_PARAMETERS:
					return "%1% parameters";
				case STRING_EMAIL_UNAVAILABLE:
					return "The email address you provided is unavailable for registration";
				case STRING_CRITICAL_DATA_UNLOADED:
					return "Critical data could not be loaded from the database";
				case STRING_X_SUCCESSFUL:
					return "%1% successful";
				case STRING_SENDMAIL:
					return "sendmail";
				case STRING_X_ERROR:
					return "%1% error";
				// case STRING_CONFIRMATION_CODE_UNDEFINED:
				// return "Invalid confirmation code";
				// case STRING_SIGNATURE_FAILED:
				// return "sodium_crypto_sign_verify_detached returned false";
				case STRING_ALREADY_WAIVED:
					return "You already have a lockout waiver in effect for this IP address";
				case STRING_CONFIRMATION_CODE_USED:
					return "The confirmation code you submitted has already been used";
				case STRING_IPV6_UNSUPPORTED:
					return "Filters for IPv6 are currently unsupported";
				case STRING_ANTI_X:
					return "Anti-%1%";
				case STRING_YOUR_IP_BANNED:
					return "Your IP address has been banned";
				case STRING_CANNOT_LOCKOUT_SELF:
					return "Operation blocked. You cannot lock yourself out.";
				case STRING_UNAUTHORIZED_X:
					return "Unauthorized %1%";
				case STRING_SEE_IP_LIST_FORM:
					return "You should be see a form that's asking you how you want to list an IP address instead of this message";
				case STRING_X_AUTHORIZED:
					return "%1% authorized";
				case STRING_ACCESS_LOG:
					return "Access log";
				case STRING_UNAUTHORIZED_X:
					return "Unauthorized %1%";
				case STRING_ARRAY_ENCODING:
					return "Array encoding";
				case STRING_SERVER_ALREADY_INITIALIZED:
					return "This server has already been initialized. You may have done it incorrectly but it's too late to change it.";
				case STRING_PLZLOGIN_MESSENGER:
					return "Please login to your account to use the messenger";
				case STRING_USE_CASE:
					return "Use case";
				case STRING_ADMIN_FORBIDDEN:
					return "This use case is of no concern to the administrator";
				case STRING_FEATURE_DEPRECATED:
					return "This feature is deprecated and no longer available";
				case STRING_BOTH_PASSWORDS:
					return "Both passwords";
				case STRING_PASSWORD_HASH:
					return "Password hash";
				case STRING_NOTHING_HERE_YET:
					return "Nothing here yet";
				case STRING_USER:
					return "User";
				case STRING_ENTER_BILLING_INFO:
					return "Enter your billing information below. This information is kept local to your device and never stored on our servers.";
				case STRING_TRY_AGAIN:
					return "Try again";
				case STRING_SIGNATORY:
					return "Signatory";
				case STRING_TARGET:
					return "Target";
				case STRING_ACCOUNT_DISABLED:
					return "Your account is disabled";
				case STRING_SUBMISSION_REJECTED_FLOOD:
					return "Your submission was rejected because you have been sending too many of them recently";
				case STRING_MESSENGER_PRIVILEGES_REVOKED:
					return "Your messenger privileges have been revoked";
				case STRING_INTEGER_OOB:
					return "Integer out of bounds";
				case STRING_SUBMISSION:
					return "Submission";
				case STRING_OBJECT:
					return "Object";
				case STRING_EXPIRED_X:
					return "Expired %1%";
				case STRING_X_LOCATION:
					return "%1% location";
				case STRING_NO_ADDRESSES:
					return "No addresses";
				case STRING_ATTACHMENTS:
					return "Attachments";
				case STRING_CHANGEMAIL_SUBMIT:
					return "You must confirm must your new email address in order to finalize the changes to your account. Instructions on completing the process have been sent to %1%. If you cannot find the message in your inbox or spam folder please contact us directly.";
				case STRING_ACTIVATION_PROHIBITED_ALREADY_LOGGED_IN:
					return "Activation prohibited: You are already logged in as %1%";
				case STRING_ALREADY_CREDITED:
					return "Your account has already been credited %1% credits";
				case STRING_USER_NOT_FOUND_POST_KEY:
					return "User with key \"%1%\" not found";
				case STRING_EDITUSER_SUCCESS_POST_KEY:
					return "Successfully updated settings for user \"%1%\"";
				case STRING_REFILL_SUCCESS_SESSION_QUANTITY:
					return "Your account has been credited %1% credits";
				// case STRING_TEMPLATE_UPDATED:
				// return "Successfully updated template \"%1%\"";
				// case STRING_TEMPLATE_INSERTED:
				// return "Successfully inserted template \"%1%\"";
				// case STRING_TEMPLATE_NOT_FOUND_GET_KEY:
				// return "Template with key \"%1%\" not found";
				case STRING_UNDEFINED_ERROR_CODE_X:
					return "Undefined error code \"%1%\"";
				case STRING_X_DATABASE:
					return "%1% database";
				case STRING_X_TEMPLATES:
					return "%1% templates";
				case STRING_CUSTOMERS:
					return "Customers";
				case STRING_ANONYMOUS_X:
					return "Anonymous %1%";
				case STRING_BUG_REPORTS:
					return "Bug reports";
				case STRING_INVENTORY:
					return "Inventory";
				case STRING_SLIDESHOW:
					return "Slideshow";
				case STRING_NEWSFEED:
					return "News feed";
				case STRING_ORDERS:
					return "Orders";
				case STRING_APPOINTMENTS:
					return "Appointments";
				case STRING_X_ADDRESSES:
					return "%1% addresses";
				case STRING_X_FIREWALL:
					return "%1% firewall";
				case STRING_YOU_ARE_LOGGED_IN_AS:
					return "You are logged in as %1%";
				case STRING_PAGE_LEFT_BLANK:
					return "This page intentionally left blank";
				case STRING_CATEGORY:
					return "Category";
				case STRING_SUBCATEGORY:
					return "Subcategory";
				case STRING_X_REQUIRED:
					return "%1% required";
				case STRING_FOR_MANUAL_REORDERING:
					return "For manual reordering";
				case STRING_SETTINGS:
					return "Settings";
				case STRING_X_METHODS:
					return "%1% methods";
				case STRING_MESSAGE:
					return "Message";
				case STRING_SEND:
					return "Send";
				case STRING_SENT:
					return "Sent";
				case STRING_LANGUAGE:
					return "Language";
				case STRING_DISMISS:
					return "Dismiss";
				case STRING_DISMISSED:
					return "Dismissed";
				case STRING_X_ALL:
					return "%1% all";
				case STRING_CHECK_FOR_X:
					return "Check for %1%";
				case STRING_UNREAD:
					return "Unread";
				case STRING_COMMENT:
					return "Comment";
				case STRING_SUBMIT_X:
					return "Submit %1%";
				case STRING_ABOUT:
					return "About";
				case STRING_TERMS_OF_SERVICE:
					return "Terms of service";
				case STRING_PRIVACY_POLICY:
					return "Privacy policy";
				case STRING_CONTACT_US:
					return "Contact us";
				case STRING_COPYRIGHT_RESERVED:
					return "Copyright %1% %2%";
				case STRING_PRESET_X:
					return "Preset %1%";
				case STRING_ALL:
					return "All";
				case STRING_REGISTER:
					return "Register";
				case STRING_USERNAME:
					return "Username";
				case STRING_EMAIL_ADDRESS:
					return "Email address";
				// case STRING_PASSWORD_12PLUS_CHAR:
				// return "Password (12+ char)";
				case STRING_CONFIRM_X:
					return "Confirm %1%";
				case STRING_OR:
					return "or";
				case STRING_SIGN_IN:
					return "Sign in";
				case STRING_CONVERSATIONS:
					return "Conversations";
				case STRING_IM_STILL_HERE:
					return "I'm still here";
				case STRING_SESSION_TIMEOUT_IMMINENT:
					return "Session timeout imminent";
				case STRING_SERVICES:
					return "Services";
				case STRING_ACCOUNT:
					return "Account";
				case STRING_X_BELOW:
					return "%1% below";
				case STRING_RESET_X:
					return "Reset %1%";
				case STRING_BY_SIGNING_IN_AGREE_TOS:
					return "By signing in, you agree to our %1%";
				case STRING_BACK_TO_X:
					return "Back to %1%";
				case STRING_FORGOT_X:
					return "Forgot your %1%?";
				case STRING_CREATE_X:
					return "Create %1%";
				case STRING_AGREE_TO_X:
					return "Agree to %1%";
				case STRING_LOG_IN:
					return "Log in";
				case STRING_CREDITS:
					return "Credits";
				case STRING_PAYMENT_METHOD_UNAVAILABLE:
					return "Payment method unavailable -- coming soon";
				case STRING_YOU_WILL_BE_REDIRECTED_TO_X:
					return "You will be redirected to %1% to process payment.";
				case STRING_NONE_OF_YOUR_PERSONAL_INFO:
					return "None of your personal information is stored on our servers.";
				case STRING_PLEASE_NOTE_SURCHARGE:
					return "Please note there is a surcharge of %1% on all purchases transacted using this payment method.";
				case STRING_QUANTITY:
					return "Quantity";
				case STRING_ENTER_X:
					return "Enter %1%";
				case STRING_OPTIONAL:
					return "Optional";
				case STRING_STREET_ADDRESS:
					return "Street address";
				case STRING_CITY:
					return "City";
				case STRING_PROVINCE:
					return "Province";
				case STRING_ZIP_POSTAL_CODE:
					return "Zip/postal code";
				case STRING_PHONE:
					return "Phone";
				case STRING_FAX:
					return "Fax";
				case STRING_OPTIONAL:
					return "Optional";
				case STRING_COUNTRY:
					return "Country";
				case STRING_IP_ADDRESSES:
					return "IP addresses";
				case STRING_WHITELISTED_X:
					return "Whitelisted %1%";
				case STRING_BLACKLISTED_X:
					return "Blacklisted %1%";
				case STRING_UNAUTHORIZED_X:
					return "Unauthorized %1%";
				case STRING_FIREWALL_NOTHING_HERE:
					return "Nothing here. This list will display IP addresses that attempt to access your account.";
				case STRING_POLICY_FORM_HEADER:
					return "With block policy enabled, unauthorized IP addresses will be unable to access your account until authorized via a link sent to your email address. This email can be disabled (this is not recommended).";
				case STRING_CIDR_NOTATION:
					return "CIDR notation";
				case STRING_NOTE:
					return "Note";
				case STRING_AUTHORIZE:
					return "Authorize";
				case STRING_BAN:
					return "Ban";
				case STRING_DELETE:
					return "Delete";
				case STRING_UPDATE:
					return "Update";
				case STRING_X_MODE:
					return "%1% mode";
				case STRING_SEND_X:
					return "Send %1%";
				case STRING_X_EMAIL:
					return "%1% email";
				case STRING_CALCULATED:
					return "Calculated";
				case STRING_ERROR:
					return "Error";
				case STRING_SUBMITTED:
					return "Submitted";
				case STRING_REJECTED:
					return "Rejected";
				case STRING_APPROVED:
					return "Approved";
				case STRING_X_REQUESTED:
					return "%1% requested";
				case STRING_REQUEST_X:
					return "Request %1%";
				case STRING_CANCELLATION:
					return "Cancellation";
				case STRING_PAY_X:
					return "Pay %1%";
				case STRING_SIGN_X:
					return "Sign %1%";
				case STRING_DECLINE_X:
					return "Decline %1%";
				case STRING_IN_PROGRESS:
					return "In progress";
				case STRING_PAYMENT_DUE:
					return "Payment due";
				case STRING_READY_FOR_X: // PICKUP_DELIVERY:
					return "Ready for %1%"; // pickup/delivery";
				case STRING_DELIVERED:
					return "Delivered";
				case STRING_RECEIVED:
					return "Received";
				case STRING_CANCELED:
					return "Canceled";
				case STRING_REFUNDED:
					return "Refunded";
				case STRING_CLOSED:
					return "Closed";
				case STRING_INVOICE:
					return "Invoice";
				case STRING_DECLINED:
					return "Declined";
				case STRING_REPORTED:
					return "Reported";
				case STRING_EN_ROUTE_TO_X:
					return "En route to %1%";
				case STRING_SHIPPED:
					return "Shipped";
				case STRING_FAILED:
					return "Failed";
				// case STRING_RETURNING_FAILED:
				// return "Returning (failed)";
				// case STRING_RETURNED_FAILED:
				// return "Returned (failed)";
				// case STRING_TRANSACTION_CANCELED:
				// return "Transaction canceled.";
				// case STRING_TRANSACTION_COMPLETE:
				// return "Transaction complete!";
				// case STRING_TRANSACTION_CLOSED:
				// return "Transaction closed.";
				case STRING_ORDER_STATE_PLACED:
					return "Order submitted and awaiting approval by administrator";
				case STRING_ORDER_STATE_REJECTED:
					return "This job was rejected by the administrator";
				case STRING_X_APPROVED:
					return "%1% approved";
				case STRING_ORDER_STATE_CANCELLATION_REQUESTED:
					return "Your cancellation request has been submitted";
				case STRING_ORDER_STATE_UNPAID_COMPLETE:
					return "Device has been serviced and is and ready for delivery once your balance of %1% credits is paid.";
				case STRING_ORDER_STATE_COMPLETED:
					return "This device has been serviced and is ready for pickup.";
				case STRING_ORDER_STATE_COMPLETED_LOCATION:
					return "Your device has been serviced and will be delivered to %1%";
				case STRING_ORDER_STATE_DELIVERED:
					return "This device has been serviced and returned to you.";
				case STRING_ORDER_STATE_REFUNDED:
					return "Transaction canceled. Your account has been refunded.";
				case STRING_ORDER_STATE_CUSTOM:
					return "Please click 'Sign invoice' to authorize payment for this service";
				// case STRING_INVOICE_DECLINED:
				// return "Invoice declined.";
				case STRING_ORDER_STATE_CALCULATED:
					return "Your device's IMEI was accepted by the remote server. Please contact the administrator to coordinate reconnection to finish servicing your device.";
				case STRING_ORDER_STATE_REPORTED:
					return "Customer reported an error; see support ticket for details";
				case STRING_REFRESH:
					return "Refresh";
				case STRING_GO_TO_PAGE:
					return "Go to page";
				case STRING_JUMP_TO_PAGE:
					return "Jump to page";
				case STRING_LIMIT:
					return "Limit";
				case STRING_ITEMS:
					return "Items";
				case STRING_UPDATED_X:
					return "Updated %1%";
				case STRING_NETWORK:
					return "Network";
				case STRING_STATUS:
					return "Status";
				case STRING_PURCHASED:
					return "Purchased";
				case STRING_UPDATED:
					return "Updated";
				case STRING_DEVICE:
					return "Device";
				case STRING_COST:
					return "Cost";
				case STRING_SERVICES_PARENTHETICAL:
					return "Service(s)";
				case STRING_PARAMETERS:
					return "Parameters";
				case STRING_NOT_APPLICABLE:
					return "N/A";
				case STRING_FILTER_X:
					return "Filter %1%";
				case STRING_NUMBER:
					return "Number";
				case STRING_IMEI:
					return "IMEI";
				case STRING_COMPLETED:
					return "Completed";
				case STRING_DEPOSIT:
					return "Deposit";
				case STRING_DEDUCTION:
					return "Deduction";
				case STRING_TIME:
					return "Time";
				case STRING_TYPE:
					return "Type";
				case STRING_X_PAYMENT:
					return "%1% payment";
				case STRING_REFUND:
					return "Refund";
				case STRING_X_PURCHASED:
					return "%1% purchased";
				case STRING_PAYMENT:
					return "Payment";
				case STRING_X_PLACED:
					return "%1% placed";
				case STRING_X_SIGNED:
					return "%1% signed";
				case STRING_UNDEFINED:
					return "Undefined";
				case STRING_CASH_USD:
					return "Cash (USD)";
				case STRING_CASH_EUR:
					return "Cash (EUR)";
				case STRING_CASH_GBP:
					return "Cash (GBP)";
				case STRING_CASH_JPY:
					return "Cash (JPY)";
				case STRING_STORE_CREDIT:
					return "Store credit";
				case STRING_ETHER:
					return "Ether";
				case STRING_NANO:
					return "Nano";
				case STRING_LINE_X:
					return "Line %1%";
				case STRING_BILLING:
					return "Billing";
				case STRING_X_INFO:
					return "%1% info";
				case STRING_CLOSE:
					return "Close";
				case STRING_LOGIN:
					return "Login";
				case STRING_EDIT:
					return "Edit";
				case STRING_CANCEL:
					return "Cancel";
				case STRING_BUG_REPORT:
					return "Bug report";
				case STRING_X_REQUEST:
					return "%1% request";
				case STRING_UNLISTED_X:
					return "Unlisted %1%";
				case STRING_DMCA:
					return "DMCA";
				case STRING_ANGRY_COMPLAINT:
					return "Angry complaint";
				case STRING_OTHER:
					return "Other";
				case STRING_ACCESSIBILITY:
					return "Accessibility";
				case STRING_CAPTCHA:
					return "Captcha";
				case STRING_COSMETICS:
					return "Cosmetics";
				case STRING_EMAIL:
					return "Email";
				case STRING_FILE:
					return "File";
				case STRING_INTERFACE:
					return "Interface";
				case STRING_KNOW_YOUR_CUSTOMER:
					return "Know your customer";
				case STRING_MESSENGER:
					return "Messenger";
				case STRING_MULTIFACTOR_AUTHENTICATION:
					return "Multifactor authentication";
				case STRING_X_PLACEMENT:
					return "%1% placement";
				// case STRING_ORDER_STATUS_UPDATE:
				// return "Order status update";
				case STRING_PURCHASING_X:
					return "Purchasing %1%";
				case STRING_REGISTRATION:
					return "Registration";
				case STRING_SEARCH:
					return "Search";
				case STRING_SECURITY:
					return "Security";
				case STRING_BUG_REPORTING:
					return "Bug reporting";
				// case STRING_SELECT_SEVERITY:
				// Debug::error("{$f} use STRING_SELECT_X");
				// return "Select severity";
				case STRING_MINOR:
					return "Minor";
				case STRING_MODERATE:
					return "Moderate";
				case STRING_SEVERE:
					return "Severe";
				case STRING_ACTIVE:
					return "Active";
				case STRING_ACCEPTED:
					return "Accepted";
				case STRING_FIXED:
					return "Fixed";
				case STRING_IRRELEVANT:
					return "Irrelevant";
				case STRING_INFO_NEEDED:
					return "Info needed";
				case STRING_OBSOLETE:
					return "Obsolete";
				case STRING_DUPLICATE:
					return "Duplicate";
				case STRING_IMPOSSIBLE:
					return "Impossible";
				case STRING_INVOICES:
					return "Invoices";
				case STRING_DESCRIBE_THE_ISSUE:
					return "Describe the issue";
				case STRING_SUBMIT_X:
					return "Submit %1%";
				case STRING_ACCEPTED:
					return "Accepted";
				case STRING_REPORT_TYPE:
					return "Report type";
				case STRING_SEVERITY:
					return "Severity";
				case STRING_SUBMITTED_BY:
					return "Submitted by";
				// case STRING_SAVE_CHANGES:
				// return "Save changes";
				// case STRING_SUBMIT_REPLY:
				// return "Submit reply";
				case STRING_X_SAID_ON_Y:
					return "%1% said on %2%";
				// case STRING_EDIT_COMMENT:
				// return "Edit comment";
				case STRING_REPLY:
					return "Reply";
				case STRING_LOG_OUT:
					return "Log out";
				case STRING_DISPLAY_NAME:
					return "Display name";
				case STRING_YOUR_DISPLAY_NAME_IS:
					return "Your display name is optionally shown in communications and cannot be used for authentication.";
				// case STRING_ENTER_PASSWORD_REVEAL_QR:
				// return "Enter your password to reveal MFA QR code and recovery seed.";
				case STRING_UNLOCK_X:
					return "Unlock %1%";
				case STRING_GENERATE_X:
					return "Generate %1%";
				case STRING_DESTROY_X:
					return "Destroy %1%";
				case STRING_X_NOTIFICATIONS:
					return "%1% notifications";
				case STRING_CURRENT_X:
					return "Current %1%";
				case STRING_X_PLUS_CHAR:
					return "%1%+ char";
				case STRING_X_OPTIONS:
					return "%1% options";
				case STRING_CHANGE_X:
					return "Change %1%";
				case STRING_THEME:
					return "Theme";
				case STRING_LIGHT:
					return "Light";
				case STRING_DARK:
					return "Dark";
				case STRING_X_ALERT:
					return "%1% alert";
				case STRING_X_ISSUE_NUM_FROM_Y_Z:
					return "%1% issue #%2% from %3% %4%";
				case STRING_REPLY_TO_X:
					return "Reply to %1%";
				case STRING_DEFERRED:
					return "Deferred";
				case STRING_INTENTIONAL:
					return "Intentional";
				case STRING_NOTIFICATIONS:
					return "Notifications";
				case STRING_DELETED:
					return "Deleted";
				case STRING_X_ON_Y:
					return "%1% on %2%";
				case STRING_MARKED_AS_X_ON_Y:
					return "Marked as %1% on %2%";
				case STRING_ISSUE_ALREADY_RESOLVED:
					return "This issue has already been resolved";
				case STRING_ISSUE_INTENTIONAL:
					return "The reported issue is an intentional feature, not a bug";
				case STRING_IMPOSSIBLE_TO_RESOLVE:
					return "Issue marked as impossible to resolve on %1%";
				case STRING_ISSUE_REJECTED_BY_ADMIN_ON_X:
					return "Issue rejected by administrator on %1%";
				case STRING_ISSUE_WAS_DEFERRED_ON_X:
					return "Issue was deferred on %1%";
				case STRING_INFO_REQUESTED_ON_X:
					return "Information requested on %1%";
				case STRING_ACTIVATE:
					return "Activate";
				case STRING_REJECT:
					return "Reject";
				case STRING_DEFER:
					return "Defer";
				case STRING_ACCEPT:
					return "Accept";
				case STRING_REQUEST_MORE_INFO:
					return "Request more info";
				case STRING_PICKUP:
					return "Pickup";
				case STRING_DELIVERY:
					return "Delivery";
				case STRING_ON_SITE_SERVICE:
					return "On site service";
				case STRING_PAST:
					return "Past";
				case STRING_UPCOMING:
					return "Upcoming";
				case STRING_CANCEL_X:
					return "Cancel %1%";
				case STRING_ESTIMATED_X:
					return "Estimated %1%";
				case STRING_AVAILABLE:
					return "Available";
				case STRING_REMOTE_X:
					return "Remote %1%";
				case STRING_X_ONLY:
					return "%1% only";
				case STRING_SINGLE_X:
					return "Single %1%";
				case STRING_MULTIPLE_X:
					return "Multiple %1%";
				case STRING_ADD_TO_X:
					return "Add to %1%";
				case STRING_FOLLOWING_IMEIS_INVALID:
					return "The following IMEIs are invalid";
				case STRING_COPY:
					return "Copy";
				case STRING_BUY_NOW:
					return "Buy now";
				case STRING_PERSONAL_X:
					return "Personal %1%";
				case STRING_GRAND_TOTAL:
					return "Grand total";
				case STRING_ESTIMATED_TIMEFRAME_X_TO_Y_LOCAL:
					return "Estimated timeframe for completion: %1% to %2% after device received";
				/*
				 * case STRING_ESTIMATED_TIMEFRAME_UNAVAIALABLE:
				 * return "Completion timeframe estimation is unavailable for the services you selected";
				 */
				case STRING_ORDER_SUMMARY_FOR_X:
					return "Order summary for %1%";
				case STRING_MORE:
					return "More";
				case STRING_NAME:
					return "Name";
				case STRING_PRICE:
					return "Price";
				case STRING_TOTAL:
					return "Total";
				case STRING_IMEIS:
					return "IMEIs";
				case STRING_IP_RANGE_CIDR:
					return "IP address/range (CIDR notation)";
				case STRING_X_DAYS:
					return "%1% days";
				case STRING_X_HOURS:
					return "%1% hours";
				case STRING_X_MINUTES:
					return "%1% minutes";
				case STRING_TIMEFRAME:
					return "Timeframe";
				case STRING_X_Y_FROM_Z:
					return "%1% %2% from %3%";
				case STRING_X_FROM_Y:
					return "%1% from %2%";
				case STRING_IP_ADDRESS:
					return "IP address";
				case STRING_LOCATION:
					return "Location";
				case STRING_TIME:
					return "Time";
				case STRING_CLICK_HERE:
					return "Click here";
				case STRING_USER_X_UPDATED_ADDRESS:
					return "User %1% has updated their address";
				case STRING_DELETE_ORDER_REFERENCED:
					return "The order this notification is referencing has been deleted";
				case STRING_NEW_MESSAGE_FROM_X:
					return "New message from %1%";
				case STRING_X_NEW_MESSAGES_FROM_Y:
					return "%1% new messages from %2%";
				case STRING_SUCCESSFUL_X:
					return "Successful %1%";
				case STRING_FAILED_X:
					return "Failed %1%";
				case STRING_X_ATTEMPT:
					return "%1% attempt";
				case STRING_NO_REASON:
					return "No reason";
				case STRING_USER_SUBMITTED:
					return "User submitted";
				case STRING_X_ATTEMPTED:
					return "%1% attempted";
				case STRING_LOCKOUT_WAIVER:
					return "Lockout waiver";
				case STRING_IP_AUTHORIZATION:
					return "IP authorization";
				case STRING_IP_BANISHMENT:
					return "IP banishment";
				case STRING_REAUTHENTICATION:
					return "Reauthentication";
				case STRING_SESSION_TIMEDOUT_MID_PURCHASE:
					return "Session timed out mid-purchase";
				case STRING_X_HAS_REPLIED_TO_YOUR_Y:
					return "%1% has replied to your %2%";
				case STRING_TRANSLATIONS:
					return "Translations";
				case STRING_PREVIOUS_ITERATOR:
					return "Previous %1%";
				case STRING_NEXT_ITERATOR:
					return "Next %1%";
				case STRING_HARDCODED:
					return "Hard-coded string";
				case STRING_TRANSLATED_X:
					return "Translated %1%";
				case STRING_REFERENCE_X:
					return "Reference %1%";
				case STRING_X_SETTINGS:
					return "%1% settings";
				case STRING_REPORT_X:
					return "Report %1%";
				case STRING_EXPAND:
					return "Expand";
				case STRING_COLLAPSE:
					return "Collapse";
				case STRING_X_SELECTION:
					return "%1% selection";
				case STRING_IPHONE:
					return "iPhone";
				case STRING_X_DEVICE:
					return "%1% device";
				case STRING_MANUFACTURER:
					return "Manufacturer";
				case STRING_MODEL:
					return "Model";
				case STRING_X_VERSION:
					return "%1% version";
				case STRING_BUILD:
					return "Build";
				case STRING_ESTIMATED_TIMEFRAME_X_TO_Y_REMOTE:
					return "Estimated timeframe for completion: %1% to %2% after a connection is established to the device";
				case STRING_ADDRESS:
					return "Address";
				case STRING_APPOINTMENT:
					return "Appointment";
				case STRING_ORDER:
					return "Order";
				case STRING_X_GALLERY:
					return "%1% gallery";
				case STRING_YOU_ARE_NOW_REGISTERED_X_Y:
					return "You are now registered. Instructions for activating your account have been sent to %1%. If you did not receive the email check your spam folder; if the problem persists please %2%.";
				case STRING_SHOW_X:
					return "Show %1%";
				case STRING_HIDE_X:
					return "Hide %1%";
				case STRING_X_HOSTS_Y:
					return "%1% host %2%";
				case STRING_X_VISITS_Y:
					return "%1% visits %2%";
				case STRING_REGISTERED_X:
					return "Registered on %1%";
				case STRING_LAST_AUTHENTICATED_FROM_IP_ADDRESS_X:
					return "Last authenticated from IP address %1%";
				case STRING_X_BALANCE:
					return "%1% balance";
				case STRING_X_HISTORY:
					return "%1% history";
				case STRING_SCHEDULE_X:
					return "Schedule %1%";
				case STRING_SESSION_STARTED_X_FROM_Y:
					return "Session started %1% from %2%";
				case STRING_LAST_SEEN_X:
					return "Last seen %1%";
				case STRING_READ_MORE:
					return "Read more";
				case STRING_SUCCESSFUL:
					return "Successful";
				case STRING_ESTIMATED_TIMEFRAME_X_TO_Y:
					return "Estimated timeframe for completion: %1% to %2%";
				case STRING_PURCHASE:
					return "Purchase";
				case STRING_X_REQUIRES_Y:
					return "%1% requires %2%";
				case STRING_ORDER_UPDATE_EMAIL:
					return "Your order on %1% has been updated and requires your attention. Please visit the following link:";
				case STRING_X_CONFIRMATION:
					return "%1% confirmation";
				case STRING_ACCOUNT_REGISTERED_EMAIL:
					return "An account has been registered at %1% for this email address. To activate your account, please visit the following URL:";
				case STRING_PASSWORD_RESET_SUBMITTED_EMAIL:
					return "A password reset request for your account was submitted to %1%. To reset your password, visit the following URL:";
				case STRING_FRAUDULENT_PASSWORD_RESET_EMAIL:
					return "If this request was sent fraudulently, you can prohibit the IP address that sent the request from attempting to access your account by visiting the following link:";
				case STRING_X_FAILURE:
					return "%1% failure";
				case STRING_LOCKOUT_EMAIL:
					return "Multiple failed attempts were made to access your %1% account using invalid credentials. To protect your account it has been temporarily locked out of authorizing logins from unapproved IP addresses; you can bypass this lockout by visiting the following link from the device with which you wish to access the site:";
				case STRING_X_CODE:
					return "%1% code";
				case STRING_ACCESS_FROM_NEW_IP_ADDRESS_X:
					return "Access from new IP address %1%";
				case STRING_UNLISTED_IP_ADDRESS_EMAIL:
					return "Someone attempted to access your account from unauthorized IP address %1% on a browser with user agent string \"%2%\". To authorize this IP address, please vist the following URL:";
				case STRING_X_CONFIRMATION:
					return "%1% confirmation";
				case STRING_CHANGE_EMAIL_ADDRESS_EMAIL:
					return "This email was sent because your email adress was submitted to %1% as a new email address for an existing account. To confirm %2% as your new email address, visit the following link:";
				case STRING_ELEMENT:
					return "Element";
				case STRING_CHILDREN:
					return "Children";
				case STRING_NEWS_POST:
					return "News post";
				case STRING_DESCRIPTION:
					return "Description";
				case STRING_ABOUT_X:
					return "About X";
				case STRING_STYLE:
					return "Style";
				case STRING_INVALIDATED_X:
					return "Invalidated %1%";
				case STRING_MFA_OTP:
					return "Multifactor authentication one-time password";
				case STRING_REGISTRATION:
					return "Registration";
				case STRING_X_EVENT:
					return "%1% event";
				case STRING_ADMINISTRATOR:
					return "Administrator";
				case STRING_ANONYMOUS_X:
					return "Anonymous %1%";
				case STRING_X_METHOD:
					return "%1% method";
				case STRING_USED_X:
					return "Used %1%";
				case STRING_PRODUCT:
					return "Product";
				case STRING_SERVICE:
					return "Service";
				case STRING_X_OPTION:
					return "%1% option";
				case STRING_X_TEMPLATE:
					return "%1% template";
				case STRING_GLOBAL_X:
					return "Global %1%";
				case STRING_X_COUNTER:
					return "%1% counter";
				case STRING_X_TREE:
					return "%1% tree";
				case STRING_ENCRYPTED_X:
					return "Encrypted %1%";
				case STRING_FILE:
					return "File";
				case STRING_IMAGE:
					return "Image";
				case STRING_X_TYPES:
					return "%1% types";
				case STRING_CARRIER:
					return "Carrier";
				case STRING_SERVER_X:
					return "Server %1%";
				case STRING_FIREWALL_SETTINGS_FOR_IP_ADDRESS_X:
					return "Firewall settings for IP address %1%";
				case STRING_TO_BOTTOM:
					return "To bottom";
				case STRING_AUTHENTICATION:
					return "Authentication";
				case STRING_X_DATA:
					return "%1% data";
				case STRING_COOKIE:
					return "Cookie";
				case STRING_USER:
					return "User";
				case STRING_LATEST:
					return "Latest";
				case STRING_SHOW:
					return "Show";
				case STRING_HIDE:
					return "Hide";
				case STRING_X_QR_CODE:
					return "%1% QR code";
				case STRING_2_CONSECUTIVE_CODES:
					return "two consecutive verification codes";
				case STRING_VERIFICATION_CODE_X:
					return "Verification code %1%";
				case STRING_ENTER_YOUR_X:
					return "Enter your %1%";
				case STRING_X_VERSION:
					return "%1% version";
				case STRING_DELETED_X:
					return "Deleted %1%";
				case STRING_MULTILINGUAL_X:
					return "Multilingual %1%";
				case STRING_PRINT:
					return "Print";
				case STRING_ITEM:
					return "Item";
				case STRING_UNIT_COST:
					return "Unit cost";
				case STRING_X_PERCENT:
					return "%1%%";
				case STRING_SELECT_X:
					return "Select %1%";
				case STRING_INHERITS:
					return "Inherits";
				case STRING_ENABLED:
					return "Enabled";
				case STRING_DISABLED:
					return "Disabled";
				case STRING_DEFAULT:
					return "Default";
				case STRING_PRIVATE:
					return "Private";
				case STRING_PUBLIC:
					return "Public";
				case STRING_REQUIRES_X:
					return "Requires %1%";
				case STRING_X_PRICE:
					return "%1% price";
				case STRING_INHERITED:
					return "Inherited";
				case STRING_CURRENT_X:
					return "Current %1%";
				case STRING_CONFIRM_DELETE_CORRESPONDENCE:
					return "Are you quite sure you want to delete your entire correspondence history with this user? This can never be undone.";
				case STRING_ENTER_MFA_OTP_FROM_APP:
					return "Enter the verification code from your authenticator app";
				case STRING_ENTER_YOUR_ACCOUNT_INFO_TO_CONTINUE:
					return "Enter your account information to continue.";
				case STRING_PLEASE_ACTIVATE:
					return "Please confirm your email address to activate your account.";
				case STRING_RESEND_X:
					return "Resend %1%";
				case STRING_CUSTOM_X:
					return "Custom %1%";
				case STRING_X_NAME:
					return "%1% name";
				case STRING_DISBURSE:
					return "Disburse";
				case STRING_DEDUCT:
					return "Deduct";
				case STRING_PAYPAL_EXPRESS:
					return "PayPal Express";
				case STRING_CASHAPP:
					return "Cash App";
				case STRING_ZELLE:
					return "Zelle";
				case STRING_LOCAL:
					return "Local";
				case STRING_DEBT_ALLOWANCE:
					return "Debt allowance";
				case STRING_VIP:
					return "VIP";
				case STRING_X_LEVEL:
					return "%1% level";
				case STRING_MUST_X:
					return "Must %1%";
				case STRING_X_IN_Y:
					return "%1% in %2%";
				case STRING_MESSAGING:
					return "Messaging";
				case STRING_X_ENABLED:
					return "%1% enabled";
				case STRING_FIXED_X:
					return "Fixed %1%";
				case STRING_INVISIBLE_TO_X:
					return "Invisible to %1%";
				case STRING_USER_HAS_NOT_ACTIVATED_ACCOUNT:
					return "User has not activated account";
				case STRING_ABSTRACT_X:
					return "Abstract %1%";
				case STRING_IS_X:
					return "Is %1%";
				case STRING_REQUIRED_X:
					return "Required %1%";
				case STRING_INVISIBLE_STRING_FOR_SORTING:
					return "Invisible string for sorting";
				case STRING_X_AVAILABLE:
					return "%1% available";
				case STRING_ADVANCE_X:
					return "Advance %1%";
				case STRING_ELIGIBLE:
					return "Eligible";
				case STRING_DISCOUNT:
					return "Discount";
				case STRING_MINIMUM_X:
					return "Minimum %1%";
				case STRING_OPTIMISTIC_X:
					return "Optimistic %1%";
				case STRING_ADD_X:
					return "Add %1%";
				case STRING_UPDATE_X:
					return "Update %1%";
				case STRING_DELETE_X:
					return "Delete %1%";
				case STRING_NAME_THIS_X:
					return "Name this %1%";
				case STRING_X_PER_Y:
					return "%1% per %2%";
				case STRING_DURATION:
					return "Duration";
				case STRING_ASPECT_RATIO:
					return "Aspect ratio";
				case STRING_SLIDE:
					return "Slide";
				case STRING_MUST_BE_X:
					return "Must be %1%";
				case STRING_LAYER:
					return "Layer";
				case STRING_TEXT:
					return "Text";
				case STRING_BLUR:
					return "Blur";
				case STRING_X_FILTER:
					return "%1% filter";
				case STRING_DROP_SHADOW:
					return "Drop shadow";
				case STRING_OPACITY:
					return "Opacity";
				case STRING_Z_INDEX:
					return "Z-index";
				case STRING_SAVE_X:
					return "Save %1%";
				case STRING_MARK_X:
					return "Mark %1%";
				case STRING_NOSHOW:
					return "No-show";
				case STRING_EDITOR_FORM:
					return "Editor form";
				case STRING_PASSWORD:
					return "Password";
				case STRING_PRESET_X:
					return "Preset %1%";
				case STRING_QR_CODE:
					return "QR code";
				case STRING_CURRENT_X:
					return "Current %1%";
				case STRING_X_REQUIRED:
					return "%1% required";
				case STRING_ANONYMOUS:
					return "Anonymous";
				case STRING_UPDATES:
					return "Updates";
				case STRING_ORDER_STATE_X_PICKUP_REQUEST_Y:
					return "Order %1% with pickup request at %2%";
				case STRING_X_CANCELED:
					return "%1% canceled";
				case STRING_TRANSACTION:
					return "Transaction";
				case STRING_AS_X:
					return "As %1%";
				case STRING_X_UNAVAILABLE:
					return "%1% unavailable";
				case STRING_BULK:
					return "Bulk";
				case STRING_INFO:
					return "Info";
				case STRING_REPORT:
					return "Report";
				case STRING_CHANGES:
					return "Changes";
				case STRING_EDIT_X:
					return "Edit %1%";
				case STRING_ELIGIBLE:
					return "Eligible";
				case STRING_DISCOUNT:
					return "Discount";
				case STRING_SERVER_BASED:
					return "Server-based";
				case STRING_AUTO:
					return "Auto";
				case STRING_X_ADDRESS:
					return "%1% address";
				case STRING_X_ACCEPTED:
					return "%1% accepted";
				case STRING_TAG:
					return "Tag";
				case STRING_TAGS:
					return "Tags";
				case STRING_X_NUM:
					return "%1% #";
				case STRING_X_NUM_Y:
					return "%1% #%2%";
				case STRING_X_IDENTICAL_TO_OLD_ONE:
					return "%1% is identical to the old one";
				case STRING_NOTIFICATION:
					return "Notification";
				case STRING_CONVERSATION:
					return "Conversation";
				case STRING_ACTIVATED:
					return "Activated";
				case STRING_CREDENTIALS:
					return 'Credentials';
				case STRING_ERROR_CODE_X:
					return "Error code \"%1%\"";
				case STRING_UNDEFINED_X:
					return "Undefined %1%";
				case STRING_UPLOAD:
					return "Upload";
				case STRING_PROFILE_X:
					return "Profile %1%";
				case STRING_NO_X:
					return "No %1%";
				case STRING_UPLOAD_X:
					return "Upload %1%";
				case STRING_NEWS:
					return "News";
				case STRING_X_VERIFICATION:
					return "%1% verification";
				case STRING_HCAPTCHA:
					return "hCaptcha";
				case STRING_X_NUMBER:
					return "%1% number";
				case STRING_UNIQUE:
					return "Unique";
				case STRING_SHIPMENT:
					return "Shipment";
				case STRING_ACTIVATION:
					return "Activation";
				case STRING_X_FEE:
					return "%1% fee";
				case STRING_PAYMENT_SURCHARGE_DESCRIPTION:
					return "We pass our fees onto you, the lowly customer";
				case STRING_CREDIT:
					return "Credit";
				case STRING_CREDIT_ITEM_DESCRIPTION:
					return "Buys services and products through " . WEBSITE_DOMAIN . " at a rate of 1 credit = 1 USD. NON-REFUNDABLE, see terms.";
				case STRING_PENDING:
					return "Pending";
				case STRING_STRIPE:
					return "Stripe";
				case STRING_MONTH:
					return "Month";
				case STRING_JANUARY:
					return "January";
				case STRING_FEBRUARY:
					return "February";
				case STRING_MARCH:
					return "March";
				case STRING_APRIL:
					return "April";
				case STRING_MAY:
					return "May";
				case STRING_JUNE:
					return "June";
				case STRING_JULY:
					return "July";
				case STRING_AUGUST:
					return "August";
				case STRING_SEPTEMBER:
					return "September";
				case STRING_OCTOBER:
					return "October";
				case STRING_NOVEMBER:
					return "November";
				case STRING_DECEMBER:
					return "December";
				case STRING_CARD:
					return "Card";
				case STRING_CVV:
					return "CVV";
				case STRING_X_DATE:
					return "%1% date";
				case STRING_EXPIRATION:
					return "Expiration";
				case STRING_CARDHOLDER:
					return "Cardholder";
				case STRING_YEAR:
					return "Year";
				case STRING_PENDING_X:
					return "Pending %1%";
				case STRING_EXTERNAL_X:
					return "External %1%";
				case STRING_X_UPDATED:
					return "%1% updated";
				case STRING_X_SUCCESSFULLY:
					return "%1% successfully";
				case STRING_PUSH:
					return "Push";
				case STRING_X_STATUS:
					return "%1% status";
				case STRING_LOCAL_X:
					return "Local %1%";
				case STRING_UNPAID:
					return "Unpaid";
				case STRING_PREFERRED_X:
					return "Preferred %1%";
				case STRING_X_SLASH_Y:
					return "%1%/%2%";
				case STRING_X_AVAILABILITY:
					return "%1% availability";
				case STRING_ENABLED_X:
					return "Enabled %1%";
				case STRING_OPTIONS:
					return "Options";
				case STRING_INVISIBLE_TO_X:
					return "Invisible to %1%";
				case STRING_PRESTIGE:
					return "Prestige";
				case STRING_CASH:
					return "Cash";
				case STRING_CREDIT_CARD:
					return "Credit card";
				case STRING_CRYPTOCURRENCY:
					return "Cryptocurrency";
				case STRING_PAYPAL_MASS_PAY:
					return "PayPal Mass Pay";
				case STRING_APPLE_PAY:
					return "Apple Pay";
				case STRING_GOOGLE_PAY:
					return "Google Pay";
				case STRING_SAMSUNG_PAY:
					return "Samsung Pay";
				case STRING_SQUARE:
					return "Square";
				case STRING_VENMO:
					return "Venmo";
				case STRING_PAYMENT_CURRENCY:
					return "Payment currency";
				case STRING_US_DOLLAR:
					return "US Dollar";
				case STRING_EURO:
					return "Euro";
				case STRING_POUND_STERLING:
					return "Pound Sterling";
				case STRING_YEN:
					return "Yen";
				case STRING_BITCOIN:
					return "Bitcoin";
				case STRING_LITECOIN:
					return "Litecoin";
				case STRING_ROLE:
					return "Role";
				case STRING_USER_X:
					return "User %1%";
				case STRING_X_FOR_Y:
					return "%1% for %2%";
				case STRING_SUCCESSFULLY_X:
					return "Successfully %1%";
				case STRING_PAYPAL:
					return "PayPal";
				case STRING_X_CENTS:
					return "%1% cents";
				case STRING_X_PLUS_Y:
					return "%1% + %2%";
				case STRING_X_AFTER_Y:
					return "%1% after %2%";
				case STRING_ALL_X:
					return "All %1%";
				case STRING_PLEASE_X:
					return "Please %1%";
				case STRING_UNKNOWN_X:
					return "Unknown %1%";
				case STRING_NOTES:
					return "Notes";
				case STRING_COLUMNS:
					return "Columns";
				case STRING_RETAIL:
					return "Retail";
				case STRING_X_PROCESSING:
					return "%1% processing";
				case STRING_INHERIT:
					return "Inherit";
				case STRING_COMPARE:
					return "Compare";
				case STRING_TEMPLATE:
					return "Template";
				case STRING_LOAD_X:
					return "Load %1%";
				case STRING_PROFIT_MARGIN:
					return "Profit margin";
				case STRING_VALUE:
					return "Value";
				case STRING_RECOVERY_SEED:
					return "Recovery seed";
				case STRING_X_AND_Y:
					return "%1% and %2%";
				case STRING_REVEAL_X:
					return "Reveal %1%";
				case STRING_X_TO_DO_Y:
					return "%1% to %2%";
				case STRING_ASSOCIATIVE_ARRAY:
					return "Associative array";
				case STRING_WHOLESALE:
					return "Wholesale";
				case STRING_DEVELOPER:
					return "Developer";
				case STRING_TRANSLATOR:
					return "Translator";
				case STRING_HELPDESK:
					return "Help Desk";
				case STRING_GUEST:
					return "Guest";
				case STRING_GROUP_MESSAGES:
					return "Group messages";
				case STRING_COMPLETE:
					return "Complete";
				case STRING_X_COMPLETE:
					return "%1% complete";
				case STRING_X_CONTENT:
					return "%1% content";
				case STRING_TITLE:
					return "Title";
				case STRING_POST_X:
					return "Post %1%";
				case STRING_X_ARE_Y:
					return "%1% are %2%";
				case STRING_X_COST:
					return "%1% cost";
				case STRING_X_NOT_FOUND:
					return "%1% not found";
				case STRING_AUTHORIZATION:
					return "Authorization";
				case STRING_WHITELIST:
					return "Whitelist";
				case STRING_FIREWALL:
					return "Firewall";
				case STRING_X_EVENTS:
					return "%1% events";
				case STRING_PRINTER_FRIENDLY:
					return "Printer-friendly";
				case STRING_BUSY:
					return "Busy";
				case STRING_AWAY:
					return "Away";
				case STRING_DONT_X:
					return "Don't %1%";
				case STRING_ONLINE:
					return "Online";
				case STRING_APPEAR_OFFLINE:
					return "Appear offline";
				case STRING_MANUAL_PAYMENT_SUBMITTED:
					return "Submission accepted. You will be notified as soon as the administrator  verifies payment.";
				case STRING_MANUAL_X:
					return "Manual %1%";
				case STRING_PAYMENTS:
					return "Payments";
				case STRING_AUTHORIZE_X:
					return "Authorize %1%";
				case STRING_APPROVE:
					return "Approve";
				case STRING_REASON_FOR_X:
					return "Reason for %1%";
				case STRING_REJECTION:
					return "Rejection";
				case STRING_REQUIRED:
					return "Required";
				case STRING_REASON_FOR_X:
					return "Reason for %1%";
				case STRING_X_FAILED:
					return "%1% failed";
				case STRING_X_AUTHORIZATION:
					return "%1% authorization";
				case STRING_X_BANISHMENT:
					return "%1% banishment";
				case STRING_X_NOTIFICATION:
					return "%1% notification";
				case STRING_PIN:
					return "Pin";
				case STRING_TOP:
					return "Top";
				case STRING_MOVE_TO_X:
					return "Move to %1%";
				case STRING_REMOVE_X:
					return "Remove %1%";
				case STRING_MESSAGES:
					return "Messages";
				case STRING_ALLOW_X:
					return "Allow %1%";
				case STRING_ISSUE_X:
					return "Issue %1%";
				case STRING_PARENT:
					return "Parent";
				case STRING_FILE_PARAMETERS:
					return "File parameters";
				case STRING_CUSTOMER:
					return "Customer";
				case STRING_MFA:
					return "MFA";
				case STRING_ENABLE_X:
					return "Enable %1%";
				case STRING_FORM:
					return "Form";
				case STRING_UNLOCK:
					return "Unlock";
				case STRING_ENTER_YOUR_X_AND_Y:
					return "Enter your %1% and %2%";
				case STRING_DISABLE_X:
					return "Disable %1%";
				case STRING_SERVER:
					return "Server";
				case STRING_SERVERS:
					return "Servers";
				case STRING_THIRD_PARTY_X:
					return "Third-party %1%";
				case STRING_ENABLE:
					return "Enable";
				case STRING_SUPPORT:
					return "Support";
				case STRING_OTP:
					return "OTP";
				case STRING_UNAVAILABLE:
					return "Unavailable";
				case STRING_PLACEHOLDER_X:
					return "Placeholder %1%";
				case STRING_X_SERVICES:
					return "%1% services";
				case STRING_X_COUNT:
					return "%1% count";
				case STRING_X_SERIES:
					return "%1% series";
				case STRING_NUM:
					return "#";
				case STRING_DATE:
					return "Date";
				case STRING_SHIPPING:
					return "Shipping";
				case STRING_BACKGROUND_X:
					return "Background %1%";
				case STRING_REGENERATE_X:
					return "Regenerate %1%";
				case STRING_XML:
					return "XML";
				case STRING_X_FILES:
					return "%1% files";
				case STRING_INSERT:
					return "Insert";
				case STRING_CONTEXT:
					return "Context";
				case STRING_CLASS:
					return "Class";
				case STRING_CHARGING_PORT:
					return "Charging port";
				case STRING_SIGNATURE:
					return "Signature";
				case STRING_XSRF:
					return "Cross-site request forgery";
				case STRING_PUSH_SUBSCRIPTION:
					return "Push subscription";
				case STRING_X_CODE:
					return "%1% code";
				case STRING_POSTS:
					return "Posts";
				case STRING_OLDER_X:
					return "Older %1%";
				case STRING_NEWER_X:
					return "Newer %1%";
				case STRING_X_CLOSED:
					return "%1% closed";
				case STRING_X_DECLINED:
					return "%1% declined";
				case STRING_X_RECEIVED:
					return "%1% received";
				case STRING_X_UNAVAIALABLE:
					return "%1% unavailable";
				case STRING_CORRESPONDENT:
					return "Correspondent";
				case STRING_X_UNKNOWN:
					return "%1% unknown";
				case STRING_TRANSACTED_X:
					return "Transacted %1%";
				case STRING_X_TOKEN:
					return "%1% token";
				case STRING_UNKNOWN:
					return "Unknown";
				case STRING_DIGITAL_X:
					return "Digital %1%";
				case STRING_CURRENCY:
					return "Currency";
				case STRING_BALANCE:
					return "Balance";
				case STRING_SESSION:
					return "Session";
				case STRING_USER_AGENT:
					return "User agent";
				case STRING_BIND_X:
					return "Bind %1%";
				case STRING_REMEMBER_X:
					return "Remember %1%";
				case STRING_FORGET_X:
					return "Forget %1%";
				case STRING_ME:
					return "Me";
				case STRING_READ_X:
					return "Read %1%";
				case STRING_TRANSLATE:
					return "Translate";
				case STRING_X_RECOVERY:
					return "%1% recovery";
				case STRING_VALIDATE:
					return "Validate";
				case STRING_X_BANNED:
					return "%1% banned";
				case STRING_SUBMIT:
					return "Submit";
				case STRING_OFFLINE:
					return "Offline";
				case STRING_INVISIBLE:
					return "Invisible";
				case STRING_X_MORE:
					return "%1% more";
				case STRING_X_SCHEDULED:
					return "%1% scheduled";
				case STRING_X_AT_Y:
					return "%1% at %2%";
				case STRING_RETRY:
					return "Retry";
				case STRING_MEDIA_MESSAGE:
					return "Media message";
				case STRING_URL:
					return "URL";
				case STRING_VIDEO:
					return "Video";
				case STRING_AUDIO:
					return "Audio";
				case STRING_ASSIGN_X:
					return "Assign %1%";
				case STRING_X_TO_Y:
					return "%1% to %2%";
				case STRING_X_PUBLIC_KEY:
					return "%1% public key";
				case STRING_X_YOUR_Y:
					return "%1% your %2%";
				case STRING_SECONDS:
					return "Seconds";
				case STRING_X_PREVIEW:
					return "%1% preview";
				case STRING_SESSIONS:
					return "Sessions";
				case STRING_TERMINATE_X:
					return "Terminate %1%";
				case STRING_OTHER_X:
					return "Other %1%";
				case STRING_SAVED_X:
					return "Saved %1%";
				case STRING_SESSION_RECOVERY_HINT_IP:
					return "Select this for additional security if you know your IP address will not change";
				case STRING_SESSION_RECOVERY_HINT_UA:
					return "With this option selected, your session cookies will only be valid on the same browser used to create the cookie";
				case STRING_READ_PAST_TENSE:
					return "Read";
				case STRING_MFA_SEED:
					return "Multifactor authentication seed";
				case STRING_CODE:
					return "Code";
				case STRING_BIT:
					return "Bit";
				case STRING_TEST_X:
					return "Test %1%";
				case STRING_FILE_X:
					return "File %1%";
				case STRING_GRANDPARENT:
					return "Grandparent";
				case STRING_A_X:
					return "A %1%";
				case STRING_I_X:
					return "I %1%";
				case STRING_AT_LEAST_X:
					return "At least %1%";
				case STRING_BE_X:
					return "Be %1%";
				case STRING_CONFIRMATION:
					return "Confirmation";
				case STRING_MATCH:
					return "Match";
				case STRING_THE_X:
					return "The %1%";
				case STRING_VALID_X:
					return "Valid %1%";
				case STRING_X_CHARACTERS:
					return "%1% characters";
				case STRING_X_MUST_Y:
					return "%1% must %2%";
				case STRING_YOU:
					return "You";
				case STRING_X_CREDENTIALS:
					return "%1% credentials";
				case STRING_YOUR_X:
					return "Your %1%";
				case STRING_X_IMAGE:
					return "%1% image";
				case STRING_X_LOGO:
					return "%1% logo";
				case STRING_X_THEME:
					return "%1% theme";
				case STRING_FEATURE:
					return "Feature";
				case STRING_STRING:
					return "String";
				case STRING_FEATURES:
					return "Features";
				case STRING_X_STRING:
					return "%1% string";
				case STRING_PAY:
					return "Pay";
				case STRING_ADVANCE:
					return "Advance";
				case STRING_X_WITH_Y:
					return "%1% with %2%";
				case STRING_X_PREFERENCE:
					return "%1% preference";
				case STRING_PINNED:
					return "Pinned";
				case STRING_X_TIMESTAMP:
					return "%1% timestamp";
				case STRING_QUOTED_X:
					return "Quoted %1%";
				case STRING_FILENAME:
					return "Filename";
				case STRING_BRIEF_X:
					return "Brief %1%";
				case STRING_ORIGINAL_X:
					return "Original %1%";
				case STRING_BASE_X:
					return "Base %1%";
				case STRING_ATTACHED_X:
					return "Attached %1%";
				case STRING_X_AXIS:
					return "%1% axis";
				case STRING_FOCAL:
					return "Focal";
				case STRING_X_DELIVERY:
					return "%1% delivery";
				case STRING_X_ROLE:
					return "%1% role";
				case STRING_X_CHANGE:
					return "%1% change";
				case STRING_SKU:
					return "SKU";
				case STRING_ISSUE:
					return "Issue";
				case STRING_X_DISMISSAL:
					return "%1% dismissal";
				case STRING_X_PAYMENT:
					return "%1% payment";
				case STRING_X_HAS_BEEN_Y:
					return "%1% has been %2%";
				case STRING_X_OR_Y:
					return "%1% or %2%";
				case STRING_CREDITED_X:
					return "Credited %1%";
				case STRING_X_CREDITS:
					return "%1% credits";
				case STRING_X_SUBMITTED:
					return "%1% submitted";
				case STRING_AWAITING_X:
					return "Awaiting %1%";
				case STRING_APPROVAL:
					return "Approval";
				case STRING_X_COMMA_Y:
					return "%1%, %2%";
				case STRING_APPROVE_X:
					return "Approve %1%";
				case STRING_REJECT_X:
					return "Reject %1%";
				case STRING_X_REQUESTS:
					return "%1% requests";
				case STRING_LOCK_OUT_X:
					return "Lock out %1%";
				case STRING_CLOSE_X:
					return "Close %1%";
				case STRING_X_QUERY:
					return "%1% query";
				case STRING_CLASSES:
					return "Classes";
				case STRING_FIELDS:
					return "Fields";
				case STRING_SEARCH_X:
					return "Search %1%";
				case STRING_TERMS:
					return "Terms";
				case STRING_ANY_X:
					return "Any %1%";
				case STRING_EXACT_X:
					return "Exact %1%";
				case STRING_PHRASE:
					return "Phrase";
				case STRING_MANUFACTURERS:
					return "Manufacturers";
				case STRING_ADMINISTRATORS:
					return "Administrators";
				case STRING_DEVICES:
					return "Devices";
				case STRING_X_NUMBERS:
					return "%1% numbers";
				case STRING_X_VERSIONS:
					return "%1% versions";
				case STRING_COMMENTS:
					return "Comments";
				case STRING_ARTICLES:
					return "Articles";
				case STRING_SLIDES:
					return "Slides";
				case STRING_SLIDESHOWS:
					return "Slideshows";
				case STRING_APPLE_CORPORATION:
					return "Apple";
				case STRING_CATEGORIES:
					return "Categories";
				case STRING_DESCRIPTIONS:
					return "Descriptions";
				case STRING_X_ATTEMPTS:
					return "%1% attempts";
				case STRING_OTPS:
					return "OTPs";
				case STRING_KEYPAIR:
					return "Keypair";
				case STRING_KEYPAIRS:
					return "Keypairs";
				case STRING_AUTOMATIC_X:
					return "Automatic %1%";
				case STRING_CREDIT_CARDS:
					return "Credit cards";
				case STRING_CHARGING_PORTS:
					return "Charging ports";
				case STRING_PRODUCTS:
					return "Products";
				case STRING_X_TREES:
					return "%1% trees";
				case STRING_DATABASE:
					return "Database";
				case STRING_ASSOCIATIVE_ARRAYS:
					return "Associative arrays";
				case STRING_FILES:
					return "Files";
				case STRING_IMAGES:
					return "Images";
				case STRING_STRINGS:
					return "Strings";
				case STRING_HARDCODED_X:
					return "Hardcoded %1%";
				case STRING_X_NAMES:
					return "%1% names";
				case STRING_X_HEADERS:
					return "%1% headers";
				case STRING_INVITES:
					return "Invites";
				case STRING_X_KEYS:
					return "%1% keys";
				case STRING_CARRIERS:
					return "Carriers";
				case STRING_THEMES:
					return "Themes";
				case STRING_COOKIES:
					return "Cookies";
				case STRING_GENERIC_X:
					return "Generic %1%";
				case STRING_OPERATING_SYSTEM:
					return "Operating system";
				case STRING_X_CODES:
					return "%1% codes";
				case STRING_USERS:
					return "Users";
				case STRING_LAYERS:
					return "Layers";
				case STRING_CASE_SENSITIVE:
					return "Case sensitive";
				case STRING_THROTTLE:
					return "Throttle";
				case STRING_PUBLIC_X:
					return "Public %1%";
				case STRING_KEY:
					return "Key";
				case STRING_KEYS:
					return "Keys";
				case STRING_ADDRESSES:
					return "Addresses";
				case STRING_ADVANCED_X:
					return "Advanced %1%";
				case STRING_TIMEZONE:
					return "Timezone";
				case STRING_AUTODETECT:
					return "Autodetect";
				case STRING_PUSH_SUBSCRIPTIONS:
					return "Push subscriptions";
				case STRING_ASCENDING:
					return "Ascending";
				case STRING_DESCENDING:
					return "Descending";
				case STRING_SORT_BY:
					return "Sort by";
				case STRING_REMOTE:
					return "Remote";
				case STRING_UNSPECIFIED_X:
					return "Unspecified %1%";
				case STRING_MARK:
					return "Mark";
				case STRING_X_AS_Y:
					return "%1% as %2%";
				case STRING_PROFILES:
					return "Profiles";
				case STRING_PROFILE:
					return "Profile";
				case STRING_SHADOW_X:
					return "Shadow %1%";
				case STRING_SHADOW:
					return "Shadow";
				case STRING_FIRST_X:
					return "First %1%";
				case STRING_LAST_X:
					return "Last %1%";
				case STRING_EXPAND_X:
					return "Expand %1%";
				case STRING_COLLAPSE_X:
					return "Collapse %1%";
				case STRING_X_ITEM:
					return "%1% item";
				case STRING_X_ITEMS:
					return "%1% items";
				case STRING_LINE:
					return "Line";
				case STRING_STORE:
					return "Store";
				case STRING_TECHNICIAN:
					return "Technician";
				case STRING_DRAFT:
					return "Draft";
				case STRING_ISSUED:
					return "Issued";
				case STRING_WITHDRAW:
					return "Withdraw";
				case STRING_INVOICE_EMAIL:
					return "Temp invoice email";
				case STRING_VIEW_X:
					return "View %1%";
				case STRING_PAID:
					return "Paid";
				case STRING_SUBTOTAL:
					return "Subtotal";
				case STRING_PDF:
					return "PDF";
				case STRING_ADDRESS_REVIEWED_EMAIL:
					return "Your address has been reviewed by an administrator and approved for shipment/delivery.";
				case STRING_X_BY_Y:
					return "%1% by %2%";
				case STRING_RESIDENT:
					return "Resident";
				case STRING_MODIFIER:
					return "Modifier";
				case STRING_X_MODIFIER:
					return "%1% modifier";
				case STRING_X_MODIFIERS:
					return "%1% modifiers";
				case STRING_PERCENTAGE:
					return "Percentage";
				case STRING_FLAT_FEE:
					return "Flat fee";
				case STRING_DISCOUNTS:
					return "Discounts";
				case STRING_X_TAX:
					return "%1% tax";
				case STRING_X_TAXES:
					return "%1% taxes";
				case STRING_FEE:
					return "Fee";
				case STRING_FEES:
					return "Fees";
				case STRING_SALES:
					return "Sales";
				case STRING_SALE:
					return "Sale";
				case STRING_TAX:
					return "Tax";
				case STRING_TAXES:
					return "Taxes";
				case STRING_ZIP:
					return "Zip";
				case STRING_X_FILE:
					return "%1% file";
				case STRING_BZIP2:
					return "BZip2";
				case STRING_GZIP:
					return "GZip";
				case STRING_RAR:
					return "Rar";
				case STRING_X_RATE:
					return "%1% rate";
				case STRING_X_RATES:
					return "%1% rates";
				case STRING_AVALARA:
					return "Avalara";
				case STRING_IMPORT_X:
					return "Import %1%";
				case STRING_CSV:
					return "CSV";
				case STRING_STATE:
					return "State";
				case STRING_X_NEXUS:
					return "%1% nexus";
				case STRING_US_STATE:
					return "US state";
				case STRING_REGION:
					return "Region";
				case STRING_US_TERRITORY:
					return "US territory";
				case STRING_X_ISSUED:
					return "%1% issued";
				case STRING_SIGN:
					return "Sign";
				case STRING_DECLINE:
					return "Decline";
				case STRING_X_PAID:
					return "%1% paid";
				case STRING_APPROPRIATION:
					return "Appropriation";
				case STRING_X_REFUNDED:
					return "%1% refunded";
				case STRING_ID:
					return "ID";
				case STRING_DUE:
					return "Due";
				case STRING_X_DUE:
					return "%1% due";
				case STRING_QTY:
					return "Qty";
				case STRING_X_TIME:
					return "%1% time";
				case STRING_PRIMARY_X:
					return "Primary %1%";
				case STRING_SET_X:
					return "Set %1%";
				case STRING_X_PAYMENTS:
					return "%1% payments";
				case STRING_AMOUNT:
					return "Amount";
				case STRING_X_OWED:
					return "%1% owed";
				case STRING_INTERSECTION:
					return "Intersection";
				case STRING_INTERSECTIONS:
					return "Intersections";
				case STRING_ROLES:
					return "Roles";
				case STRING_SHARED_X:
					return "Shared %1%";
				case STRING_BACKUP:
					return "Backup";
				case STRING_WALLET:
					return "Wallet";
				case STRING_WEB:
					return "Web";
				case STRING_MONOLITHIC:
					return "Monolithic";
				case STRING_PASSWORDS:
					return "Passwords";
				case STRING_DOMAIN:
					return "Domain";
				case STRING_X_USERS:
					return "%1% users";
				case STRING_X_RECORD:
					return "%1% record";
				case STRING_X_RECORDS:
					return "%1% records";
				case STRING_ERROR_PROCESSING_REQUEST:
					return "There was an error processing your request, please try again.";
				case STRING_SESSION_HIJACK_WARNING:
					return "This session has been terminated because it has been accessed by a device with an incorrect %1%, and you configured your account settings to do so under these circumstances.";
				case STRING_X_PREVENTION:
					return "%1% prevention";
				case STRING_X_HIJACK:
					return "%1% hijack";
				case STRING_LOGGED_IN:
					return "Logged in";
				case STRING_STAY_X:
					return "Stay %1%";
				case STRING_X_SUBSCRIPTION:
					return "%1% subscription";
				case STRING_X_SUBSCRIPTIONS:
					return "%1% subscriptions";
				case STRING_X_WHEN_Y:
					return "%1% when %2%";
				case STRING_X_ME:
					return "%1% me";
				case STRING_NOTIFY:
					return "Notify";
				case STRING_RESULTS:
					return "Results";
				case STRING_UNSUBSCRIBE:
					return "Unsubscribe";
				case STRING_NOW_X:
					return "Now %1%";
				case STRING_SERVICE_AVAILABILITY_EMAIL:
					return "You are receiving this email because you asked to be notified when the service %1% became available.";
				case STRING_Y_TO_DO_X:
					return "%2% to %1%";
				case STRING_VISIT_X:
					return "Visit %1%";
				case STRING_THE_FOLLOWING_X:
					return "The following %1%";
				case STRING_LINK:
					return "Link";
				case STRING_THIS_X:
					return "This %1%";
				case STRING_ALLOW:
					return "Allow";
				case STRING_BLOCK:
					return "Block";
				case STRING_DO_X_TO_Y:
					return "%1% %2%";
				case STRING_DO_X:
					return "Do %1%";
				case STRING_NOTHING:
					return "Nothing";
				case STRING_X_IS_Y:
					return "%1% is %2%";
				case STRING_IT:
					return "It";
				case STRING_X_HAS_Y:
					return "%1% has %2%";
				case STRING_AN_X:
					return "An %1%";
				case STRING_EVENT:
					return "Event";
				case STRING_DISBURSEMENT:
					return "Disbursement";
				case STRING_IMBURSEMENT:
					return "Imbursement";
				case STRING_EXPENDITURE:
					return "Expenditure";
				case STRING_X_COOKIES:
					return "%1% cookies";
				case STRING_X_COOKIE:
					return "%1% cookie";
				case STRING_DEFAULT_X:
					return "Default %1%";
				case STRING_X_POLICY:
					return "%1% policy";
				case STRING_SET_X_TO_Y:
					return "Set %1% to %2%";
				case STRING_AUTHORIZED:
					return "Authorized";
				case STRING_UNAUTHORIZED:
					return "Unauthorized";
				case STRING_REASON:
					return "Reason";
				case STRING_ATTENTION:
					return "Attention";
				case STRING_HOME:
					return "Home";
				case STRING_X_SCREEN:
					return "%1% screen";
				case STRING_ADD:
					return "Add";
				case STRING_CLEAR_X:
					return "Clear %1%";
				case STRING_CACHE:
					return "Cache";
				case STRING_APCu:
					return "APCu";
				case STRING_X_SOURCES:
					return "%1% sources";
				case STRING_X_SOURCE:
					return "%1% source";
				case STRING_GROUP:
					return "Group";
				case STRING_GROUPS:
					return "Groups";
				case STRING_INVITATION:
					return "Invitation";
				case STRING_INVITATIONS:
					return "Invitations";
				case STRING_SESSION_HIJACK_HINT_UA:
					return "With this option enabled, your session will automatically terminate if your user agent string differs from what it was when you logged in. Your user agent string is \"{$_SERVER['HTTP_USER_AGENT']}\"";
				case STRING_SESSION_HIJACK_HINT_IP:
					return "With this option enabled, your session will automatically terminate if your IP address changes from what it was when you logged in. Your IP address is {$_SERVER['REMOTE_ADDR']}";
				case STRING_SESSION_RECOVERY_FORM_HEADER:
					return "By choosing 'Remember Me', your session will automatically refresh upon expiration. You will remain logged in until you logout, terminate your saved session or delete this site's cookies from your device.";
				case STRING_SESSION_HIJACK_FORM_HEADER:
					$dur = SESSION_TIMEOUT_SECONDS / 60;
					return "Sessions last {$dur} minutes or until you delete your cookies.";
				case STRING_X_COLLISION:
					return "%1% collision";
				case STRING_BLOCKED:
					return "Blocked";
				case STRING_SINGULAR_NOUN_X_PAST_TENSE_VERB_Y:
					return "%1% %2%";
				case STRING_PROCESS_X:
					return "Process %1%";
				case STRING_SCHEDULED:
					return "Scheduled";
				case STRING_MARKED_X:
					return "Marked %1%";
				case STRING_X_APPOINTMENT:
					return "%1% appointment";
				case STRING_FAILED_LOGIN_EMAIL:
					return "Someone attempted and failed to access your %1% account on %2% from a device with IP address %3% and user agent string \"%4%\".";
				case STRING_EXTERNAL_PAYMENT_EMAIL_SUBMITTED:
					return "User %1% made a $%2% %3% payment that requires manual authorization.";
				case STRING_EXTERNAL_PAYMENT_EMAIL_APPROVED:
					return "Your $%1% %2% payment with ID \"%3%\" on %4% has been approved.";
				case STRING_EXTERNAL_PAYMENT_EMAIL_REJECTED:
					return "Your $%1% %2% payment with ID \"%3%\" on %4% has been rejected for the following reason: %5%";
				case STRING_EXTERNAL_PAYMENT_EMAIL_REFUNDED:
					return "Your $%1% %2% payment with ID \"%3%\" on %4% has been refunded.";
				case STRING_CUSTOM:
					return "Custom";
				case STRING_PERMISSION:
					return "Permission";
				case STRING_TABLET:
					return "Tablet";
				case STRING_APPLIED_X:
					return "Applied %1%";
				case STRING_COMBINED_X:
					return "Combined %1%";
				case STRING_OFF:
					return "Off";
				case STRING_BACK:
					return "Back";
				case STRING_ADDRESS_SUBMITTED_EMAIL:
					return "User %1% has submitted an address for your consideration";
				case STRING_PROFIT_MARGINS:
					return "Profit margins";
				case STRING_X_DECLARATIONS:
					return "%1% declarations";
				case STRING_X_PERMISSION:
					return "%1% permission";
				case STRING_USERNAMES:
					return "Usernames";
				case STRING_FRAUDULENT_X:
					return "Fraudulent %1%";
				case STRING_MESSAGE_EMAIL:
					return "%1% has sent you a message on %2%.";
				case STRING_MESSAGE_EMAIL_PREVIEW:
					return "%1% has sent you the following message on %2%: \"%3%\"";
				case STRING_FRONT_PAGE_NAVIGATION_PROMPT:
					return "Select your device to get started";
				case STRING_CLEAR:
					return "Clear";
				case STRING_JAVASCRIPT_REQUIRED:
					return "JavaScript is required to use this feature";
				case STRING_ABOUT_US:
					return CONST_ABOUT_US_STRING;
				case STRING_US:
					return "Us";
				case STRING_VIEW:
					return "View";
				case STRING_COOKIES_DELETED:
					return "All cookies have been deleted";
				case STRING_DELETE_COOKIES_PAGE:
					return "Click \"Delete all cookies\" to remove all cookies placed on your device by this website. This will terminate your session.";
				case STRING_FORBIDDEN:
					return "Forbidden";
				case STRING_REFRESH_X:
					return "Refresh %1%";
				case STRING_TEST:
					return "Test";
				case STRING_READ:
					return "Read";
				case STRING_SELECT:
					return "Select";
				case STRING_MOBILE_NETWORK_SERVICE_PLAN:
					return "Mobile network service plan";
				case STRING_MOBILE_NETWORK_SERVICE_PLANS:
					return "Mobile network service plans";
				case STRING_DOWNLOAD_X:
					return "Download %1%";
				case STRING_REVIEW:
					return "Review";
				case STRING_REPLIED:
					return "Replied";
				case STRING_LAST:
					return "Last";
				default:
					$err = "Undefined string ID \"{$string_id}\"";
					Debug::error("{$f} {$err}");
					return $err;
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}

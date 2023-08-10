<?php
namespace JulianSeymour\PHPWebApplicationFramework;

define("CONST_ALL", "all");
define("CONST_BEFORE", "before");
define("CONST_AFTER", "after");
define("CONST_AUTO", "auto");
define("CONST_DEFAULT", "default");
define("CONST_ERROR", "error");
define("CONST_NONE", "none");
define("CONST_OFF", "off");
define("CONST_ON", "on");
define("CONST_TEMPLATE", "template");
define("CONST_UNDEFINED", "undefined");
define("CONST_UNKNOWN", "unknown");

define("VERSION_UNKNOWN", 0);

define("PROGRESSIVE_HYPERLINK_KEY", "pwa");

define("HTTP_REQUEST_METHOD_CONNECT", "CONNECT");
define("HTTP_REQUEST_METHOD_DELETE", "DELETE");
define("HTTP_REQUEST_METHOD_GET", "GET");
define("HTTP_REQUEST_METHOD_HEAD", "HEAD");
define("HTTP_REQUEST_METHOD_OPTIONS", "OPTIONS");
define("HTTP_REQUEST_METHOD_PATCH", "PATCH");
define("HTTP_REQUEST_METHOD_POST", "POST");
define("HTTP_REQUEST_METHOD_PUT", "PUT");
define("HTTP_REQUEST_METHOD_TRACE", "TRACE");

define("EXECUTION_STATE_UNDEFINED", 0);
define("EXECUTION_STATE_INITIAL", 1);
define("EXECUTION_STATE_HEADERS_SENT", 2);
define("EXECUTION_STATE_AUTHENTICATED", 3);
define("EXECUTION_STATE_LOADED", 4);
define("EXECUTION_STATE_EXECUTED", 5);
define("EXECUTION_STATE_RESPONDING", 6);
define("EXECUTION_STATE_RESPONDED", 7);
define("EXECUTION_STATE_TERMINATED", 8);
define("EXECUTION_STATE_ERROR", 9);

define("MFA_STATUS_DISABLED", 0);
define("MFA_STATUS_ENABLED", 1);

define("SQL_PRIMITIVE_INT", 'INT');
define("SQL_PRIMITIVE_TINYINT", 'TINYINT');
define("SQL_PRIMITIVE_BIGINT", 'BIGINT');
define("SQL_PRIMITIVE_ENUM", "ENUM");
define("SQL_PRIMITIVE_SET", 'SET');
define("SQL_PRIMITIVE_CHAR", 'CHAR');
define("SQL_PRIMITIVE_FLOAT", 'FLOAT');
define("SQL_PRIMITIVE_DOUBLE", 'DOUBLE');
define("SQL_PRIMITIVE_VARCHAR", 'VARCHAR');
define("SQL_PRIMITIVE_TEXT", 'TEXT');
define("SQL_PRIMITIVE_TINYTEXT", 'TINYTEXT');
define("SQL_PRIMITIVE_MEDIUMTEXT", 'MEDIUMTEXT');
define("SQL_PRIMITIVE_LONGTEXT", 'LONGTEXT');
define("SQL_PRIMITIVE_DECIMAL", 'DECIMAL');
define("SQL_PRIMITIVE_DATE", 'DATE');
define("SQL_PRIMITIVE_DATETIME", 'DATETIME');
define("SQL_PRIMITIVE_TIME", 'TIME');
define("SQL_PRIMITIVE_TIMESTAMP", 'TIMESTAMP');

define("ACCESS_TYPE_ACTIVATION", "activation");
define("ACCESS_TYPE_RESET", "resetPassword");
define("ACCESS_TYPE_CHANGE_EMAIL", "changeEmail");
define("ACCESS_TYPE_LOCKOUT_WAIVER", "unlockAccount");
define("ACCESS_TYPE_UNLISTED_IP_ADDRESS", "unlistedIP");
define("ACCESS_TYPE_BACKUP", "backup"); // workaround -- do not treat as a valid record type
define("ACCESS_TYPE_REAUTHENTICATION", "reauth");
define("ACCESS_TYPE_LOGIN", "login");

define("SEARCH_BAR_PRODUCT", 0);
define("SEARCH_BAR_IMEI", 1);
define("SEARCH_BAR_MESSAGE", 2);
define("SEARCH_BAR_NEWS", 3);
define("SEARCH_BAR_HARDWARE", 4);
define("SEARCH_BAR_SITE", 5);
define("SEARCH_BAR_USER", 6);
define("SEARCH_BAR_PAYPAL", 7);
define("SEARCH_BAR_HISTORY", 8);

define("EXPANSION_DIRECTION_UNDEFINED", 0);
define("EXPANSION_DIRECTION_ROOT", 1);
define("EXPANSION_DIRECTION_LEAF", 2);

define("KYC_INITIAL", 0); // user has not submitted KYC documents
define("KYC_UNVERIFIED", 1); // user has provided their private data, but it is unverified
define("KYC_SUBMITTED", 2); // user has submitted KYC documents, awaiting review
define("KYC_REJECTED", 3); // admin has rejected user KYC doucments
define("KYC_REVISE", 4); // admin has suspended KYC document review so user can make adjustments
define("KYC_ACCEPTED", 5); // admin has accepted user KYC documents
define("KYC_REVOKED", 6); // admin has accepted user KYC documents, then rejected them again

define("ACCOUNT_TYPE_ERROR", "error");
define("ACCOUNT_TYPE_ADMIN", "admin");
define("ACCOUNT_TYPE_USER", "user");
define("ACCOUNT_TYPE_GUEST", "guest");
// define("ACCOUNT_TYPE_ORGANIZATION", 4);
// define("ACCOUNT_TYPE_WHOLESALE", ACCOUNT_TYPE_ORGANIZATION);
define("ACCOUNT_TYPE_DEVELOPER", "developer");
define("ACCOUNT_TYPE_TRANSLATOR", "translator");
define("ACCOUNT_TYPE_HELPDESK", "helpDesk");
define("ACCOUNT_TYPE_GROUP", "group");
define("ACCOUNT_TYPE_SHADOW", "shadow");
define("ACCOUNT_TYPE_TEMPLATE", CONST_TEMPLATE);
define("ACCOUNT_TYPE_UNDEFINED", CONST_UNDEFINED);

// define("NOTIFICATION_STATE_UNDEFINED", 0);
define("NOTIFICATION_STATE_UNREAD", "unread"); // user has not seen notification
define("NOTIFICATION_STATE_DISMISSED", "dismissed"); // user has seen or dismissed a message/non-interactive order update, or completed their obligations for an interactive/mandatory notification
                                                     // define("NOTIFICATION_STATE_ATTENTION", 3); //user cannot dismiss except by doing whatever is asked of them
define("NOTIFICATION_STATE_SENT", NOTIFICATION_STATE_DISMISSED); // appears in conversation list but not notifications widget
define("NOTIFICATION_STATE_READ", NOTIFICATION_STATE_DISMISSED);

// define("NOTIFICATION_TYPE_UNDEFINED", 0);
define("NOTIFICATION_TYPE_SECURITY", "security"); // 5
define("NOTIFICATION_TYPE_REGISTRATION", "registration"); // 9
define("NOTIFICATION_TYPE_UNLISTED_IP", "unlistedIP"); // 10
define("NOTIFICATION_TYPE_LOCKOUT", "lockout"); // 11
define("NOTIFICATION_TYPE_LOGIN", "login");
define("NOTIFICATION_TYPE_CHANGE_EMAIL", "changeEmail"); // 12
define("NOTIFICATION_TYPE_TEMPLATE", "template"); // 14
define("NOTIFICATION_TYPE_TEST", "test"); // 15
define("NOTIFICATION_TYPE_ALL", "all"); // 16
define("NOTIFICATION_TYPE_RESET_PASSWORD", "resetPassword"); // 17

// define("NOTIFICATION_TYPE_CUSTOM", NOTIFICATION_TYPE_UNDEFINED);

define("NOTIFICATION_ENUM_UNDEFINED", 0);

define("SIGNAGE_BIT_POSITIVE", 0);
define("SIGNAGE_BIT_NEGATIVE", 1);

define("EXTENSION_JPG", "jpg");
define("EXTENSION_PNG", "png");
define("EXTENSION_GIF", "gif");
define("EXTENSION_JPEG", "jpeg");

define("MIME_TYPE_7ZIP", "application/x-7z-compressed");
define("MIME_TYPE_BZIP2", "application/x-bzip2");
define("MIME_TYPE_GIF", 'image/gif');
define("MIME_TYPE_GZIP", "application/gzip");
define("MIME_TYPE_JPEG", 'image/jpeg');
define("MIME_TYPE_JPG", MIME_TYPE_JPEG);
define("MIME_TYPE_OCTET_STREAM", 'application/octet-stream');
define("MIME_TYPE_PDF", "application/pdf");
define("MIME_TYPE_PLAINTEXT", "text/plain");
define("MIME_TYPE_PNG", 'image/png');
define("MIME_TYPE_RAR", "application/vnd.rar");
define("MIME_TYPE_ZIP", "application/zip");

define("FINITE_STATE_UNAUTHORIZED", "unauthorized");
define("FINITE_STATE_ERROR", "error");

define("NOTIFICATION_MODE_UPDATE_EXISTING", 1);
define("NOTIFICATION_MODE_SEND_NEW", 2);

define("USER_ROLE_SENDER", "sender");
define("USER_ROLE_RECIPIENT", "recipient");
define("USER_ROLE_BUYER", "buyer");
define("USER_ROLE_SELLER", "seller");
define("USER_ROLE_HOST", "host");
define("USER_ROLE_VISITOR", "visitor");
define("USER_ROLE_COLLECTOR", "collector");
define("USER_ROLE_DEBTOR", "debtor");
define("USER_ROLE_FOUNDER", "founder");
define("USER_ROLE_MEMBER", "member");
define("USER_ROLE_STRANGER", "stranger");
define("USER_ROLE_PARIAH", "pariah");
define("USER_ROLE_OWNER", "owner");
define("USER_ROLE_ERROR", CONST_ERROR);

define("SUPERGLOBAL_UNDEFINED", 0);
define("SUPERGLOBAL_POST", 1);
define("SUPERGLOBAL_GET", 2);
define("SUPERGLOBAL_SESSION", 3);
define("SUPERGLOBAL_COOKIE", 4);
define("SUPERGLOBAL_SERVER", 5);
define("SUPERGLOBAL_FILE", 6);

define("LOGIN_TYPE_UNDEFINED", 0);
define("LOGIN_TYPE_FULL", 1);
define("LOGIN_TYPE_PARTIAL", 2);

define("LANGUAGE_AFRIKAANS", 'af');
define("LANGUAGE_ARABIC", 'ar');
define("LANGUAGE_ARAGONESE", 'ar');
define("LANGUAGE_ASSAMESE", 'as');
define("LANGUAGE_ASTURIAN", 'ast');
define("LANGUAGE_AZERBAIJANI", 'az');
define("LANGUAGE_BULGARIAN", 'bg');
define("LANGUAGE_BELARUSIAN", 'be');
define("LANGUAGE_BENGALI", 'bn');
define("LANGUAGE_BRETON", 'br');
define("LANGUAGE_BOSNIAN", 'bs');
define("LANGUAGE_CATALAN", 'ca');
define("LANGUAGE_CHECHEN", 'ce');
define("LANGUAGE_CHAMORRO", 'ch');
define("LANGUAGE_CORSICAN", 'co');
define("LANGUAGE_CREE", 'cr');
define("LANGUAGE_CZECH", 'cs');
define("LANGUAGE_CHUVASH", 'cv');
define("LANGUAGE_WELSH", 'cy');
define("LANGUAGE_DANISH", 'da');
define("LANGUAGE_GERMAN", 'de');
define("LANGUAGE_GREEK", 'el');
define("LANGUAGE_ENGLISH", 'en');
define("LANGUAGE_ESPERANTO", 'eo');
define("LANGUAGE_SPANISH", 'es');
define("LANGUAGE_ESTONIAN", 'et');
define("LANGUAGE_BASQUE", 'eu');
define("LANGUAGE_FARSI", 'fa');
define("LANGUAGE_PERSIAN", 'fa');
define("LANGUAGE_FAEROESE", 'fo');
define("LANGUAGE_FINNISH", 'fi');
define("LANGUAGE_FIJIAN", 'fj');
define("LANGUAGE_FRENCH", 'fr');
define("LANGUAGE_FRIULIAN", 'fur');
define("LANGUAGE_FRISIAN", 'fy');
define("LANGUAGE_IRISH", 'ga');
define("LANGUAGE_GAELIC", 'gd');
define("LANGUAGE_SCOTS_GAELIC", 'gd');
define("LANGUAGE_GALACIAN", 'gl');
define("LANGUAGE_GUJURATI", 'gu');
define("LANGUAGE_HEBREW", 'he');
define("LANGUAGE_HINDI", 'hi');
define("LANGUAGE_CROATIAN", 'hr');
define("LANGUAGE_UPPER_SORBIAN", 'hsb');
define("LANGUAGE_HATIAN", 'ht');
define("LANGUAGE_HUNGARIAN", 'hu');
define("LANGUAGE_ARMENIAN", 'hy');
define("LANGUAGE_INDONESIAN", 'id');
define("LANGUAGE_ICELANDIC", 'is');
define("LANGUAGE_INUKTITUT", 'iu');
define("LANGUAGE_ITALIAN", 'it');
define("LANGUAGE_JAPANESE", 'ja');
define("LANGUAGE_YIDDISH", 'ji');
define("LANGUAGE_GEORGIAN", 'ka');
define("LANGUAGE_KAZAKH", 'kk');
define("LANGUAGE_KHMER", 'km');
define("LANGUAGE_KANNADA", 'kn');
define("LANGUAGE_KOREAN", 'ko');
define("LANGUAGE_KASHMIRI", 'ks');
define("LANGUAGE_KIRGHIZ", 'ky');
define("LANGUAGE_LATIN", 'la');
define("LANGUAGE_LUXEMBOURGISH", 'lb');
define("LANGUAGE_LITHUANIAN", 'lt');
define("LANGUAGE_LATVIAN", 'lv');
define("LANGUAGE_MAORI", 'mi');
define("LANGUAGE_FYRO_MACEDONIAN", 'mk');
define("LANGUAGE_MALAYALAM", 'ml');
define("LANGUAGE_MODAVIAN", 'mo');
define("LANGUAGE_MARATHI", 'mr');
define("LANGUAGE_MALAY", 'ms');
define("LANGUAGE_MALTESE", 'mt');
define("LANGUAGE_BURMESE", 'my');
define("LANGUAGE_NORWEGIAN_BOKMAL", 'nb');
define("LANGUAGE_NEPALI", 'ne');
define("LANGUAGE_NDONGA", 'ng');
define('LANGUAGE_DUTCH', 'nl');
define("LANGUAGE_NORWEGIAN_NYNORSK", 'nn');
define("LANGUAGE_NORWEGIAN", 'no');
define("LANGUAGE_NAVAJO", 'nv');
define("LANGUAGE_OCCITAN", 'oc');
define("LANGUAGE_ORIYA", 'or');
define("LANGUAGE_OROMO", 'om');
define("LANGUAGE_PUNJABI", 'pa');
define("LANGUAGE_POLISH", 'pl');
define("LANGUAGE_PORTUGUESE", 'pt');
define("LANGUAGE_QUECHUA", 'qu');
define("LANGUAGE_RHAETO_ROMANIC", 'rm');
define("LANGUAGE_ROMANIAN", 'ro');
define("LANGUAGE_RUSSIAN", 'ru');
define("LANGUAGE_SANSKRIT", 'sa');
define("LANGUAGE_SORBIAN", 'sb');
define("LANGUAGE_SARDINIAN", 'sc');
define("LANGUAGE_SINDHI", 'sd');
define("LANGUAGE_SANGO", 'sg');
define("LANGUAGE_SINGHALESE", 'si');
define("LANGUAGE_SLOVAK", 'sk');
define("LANGUAGE_SLOVENIAN", 'sl');
define("LANGUAGE_SOMANI", 'so');
define("LANGUAGE_ALBANIAN", 'sq');
define("LANGUAGE_SERBIAN", 'sr');
define("LANGUAGE_SWEDISH", 'sv');
define("LANGUAGE_SWAHILI", 'sw');
define("LANGUAGE_SUTU", 'sx');
define("LANGUAGE_SAMI_LAPPISH", 'sz');
define("LANGUAGE_TAMIL", 'ta');
define("LANGUAGE_TELUGA", 'te');
define("LANGUAGE_THAI", 'th');
define("LANGUAGE_TIGRE", 'tig');
define("LANGUAGE_TURKMEN", 'tk');
define("LANGUAGE_KLINGON", 'tlh');
define("LANGUAGE_TSWANA", 'tn');
define("LANGUAGE_TATAR", 'tt');
define("LANGUAGE_TURKISH", 'tr');
define("LANGUAGE_TSONGA", 'ts');
define("LANGUAGE_UKRANIAN", 'uk');
define("LANGUAGE_URDU", 'ur');
define("LANGUAGE_VENDA", 've');
define("LANGUAGE_VIETNAMESE", 'vi');
define("LANGUAGE_VOLAPUK", 'vo');
define("LANGUAGE_WALLOON", 'wa');
define("LANGUAGE_XHOSA", 'xh');
define("LANGUAGE_CHINESE", 'zh');
define("LANGUAGE_ZULU", 'zu');
define("LANGUAGE_DERP", "derp");
define("LANGUAGE_UNDEFINED", null);
define("LANGUAGE_DEFAULT", LANGUAGE_ENGLISH);

define("OPERATOR_EQUALS", "=");
define("OPERATOR_EQUALSEQUALS", "==");
define("OPERATOR_LESSTHAN", "<");
define("OPERATOR_LESSTHAN_STRING", "less than");
define("OPERATOR_GREATERTHAN", ">");
define("OPERATOR_LESSTHANEQUALS", "<=");
define("OPERATOR_GREATERTHANEQUALS", ">=");
define("OPERATOR_LESSTHANGREATERTHAN", "<>");
define("OPERATOR_IS_NULL", "is null");
define("OPERATOR_IS_NOT_NULL", "is not null");
define("OPERATOR_IN", "in");
define("OPERATOR_NOT_IN", "not in");
define("OPERATOR_PLUS", '+');
define("OPERATOR_MINUS", '-');
define("OPERATOR_MULT", '*');
define("OPERATOR_DIVISION", '/');
define("OPERATOR_MODULO", '%');
define("OPERATOR_NOTEQUALS", "!=");
define("OPERATOR_AND_BITWISE", "&");
define("OPERATOR_AND", OPERATOR_AND_BITWISE);
define("OPERATOR_AND_BOOLEAN", "&&");
define("OPERATOR_ANDAND", OPERATOR_AND_BOOLEAN);
define("OPERATOR_AND_DATABASE", "AND");
define("OPERATOR_OR_BITWISE", "|");
define("OPERATOR_OR_BOOLEAN", "||");
define("OPERATOR_OR", OPERATOR_OR_BITWISE);
define("OPERATOR_OROR", OPERATOR_OR_BOOLEAN);
define("OPERATOR_OR_DATABASE", "OR");
define("OPERATOR_XOR", "^");
define("OPERATOR_NOTEQUALSEQUALS", "!==");
define("OPERATOR_IDENTITY", "===");
define("OPERATOR_STARTS_WITH", "startsWith");
define("OPERATOR_ENDS_WITH", "endsWith");
define("OPERATOR_CONTAINS", "contains");
define("OPERATOR_LIKE", "like"); // changes show columns statement to use like instead of where

define("INPUT_TYPE_UNDEFINED", "undefined");
define("INPUT_TYPE_BUTTON", "button");
define("INPUT_TYPE_CHECKBOX", "checkbox");
define("INPUT_TYPE_COLOR", "color");
define("INPUT_TYPE_DATE", "date");
define("INPUT_TYPE_DATETIME_LOCAL", "datetime-local");
define("INPUT_TYPE_EMAIL", "email");
define("INPUT_TYPE_FILE", "file");
define("INPUT_TYPE_HIDDEN", "hidden");
define("INPUT_TYPE_IMAGE", "image");
define("INPUT_TYPE_MONTH", "month");
define("INPUT_TYPE_NUMBER", "number");
define("INPUT_TYPE_PASSWORD", "password");
define("INPUT_TYPE_RADIO", "radio");
define("INPUT_TYPE_RANGE", "range");
define("INPUT_TYPE_RESET", "reset");
define("INPUT_TYPE_SEARCH", "search");
define("INPUT_TYPE_SUBMIT", "submit");
define("INPUT_TYPE_TEL", "tel");
define("INPUT_TYPE_TEXT", "text");
define("INPUT_TYPE_TIME", "time");
define("INPUT_TYPE_URL", "url");
define("INPUT_TYPE_WEEK", "week");
define("INPUT_TYPE_SELECT", "select");
define("INPUT_TYPE_TEXTAREA", "textarea");

define("POLICY_NONE", "none");
define("POLICY_DEFAULT", POLICY_NONE);
define("POLICY_ALLOW", "allow");
define("POLICY_BLOCK", "block");
define("POLICY_REQUIRE", "require");

define("BECAUSE_NOREASON", "noReason"); // uninitialized
define("BECAUSE_USER", "userSubmitted"); // user submitted IP address
define("BECAUSE_REGISTER", "registration"); // automatically whitelisted upon registration
define("BECAUSE_LOGIN", "login"); // login attempted
define("BECAUSE_FORGOTNAME", "forgotName"); // forgot name email sent
define("BECAUSE_FORGOTPASS", "forgotPassword"); // forgot password email sent
define("BECAUSE_RESET", "resetPassword"); // hard password reset attempted
define("BECAUSE_WAIVER", "lockoutWaiver"); // attempted to validate lockout waiver confirmation code
define("BECAUSE_WHITELIST", "whitelist"); // manually whitelisted an IP address through authorize.php
define("BECAUSE_BLACKLIST", "blacklist"); // manually blacklisted an IP address through filter.php
define("BECAUSE_REAUTH", "reauthentication"); // user IP changed mid session
define("BECAUSE_TIMEOUT", "timeout"); // session timed out on paypal's site, returned with new IP
define("BECAUSE_REFILL", "refill");
define("BECAUSE_CHECKOUT", "checkout");
define("BECAUSE_REFUND", "refund");
define("BECAUSE_CHANGE_EMAIL", "changeEmail");
define("BECAUSE_BACKUP", "backup");
define("BECAUSE_UNLISTED_IP", "unlistedIP");
define("BECAUSE_LOCKOUT", "lockout");
define("BECAUSE_ACTIVATION", "activation");
define("BECAUSE_IPAUTH", "classifyIP");
define("BECAUSE_HONEYPOT_SIMPLE", "honeypot1");
define("BECAUSE_HONEYPOT_COMPLEX", "honeypot2");
define("BECAUSE_VULNERABILITY_SCANNER", "vuln");

define("PERSISTENCE_MODE_UNDEFINED", 0);
define("PERSISTENCE_MODE_VOLATILE", 1); // not stored -- datum is used for indirection
define("PERSISTENCE_MODE_DATABASE", 2);
define("PERSISTENCE_MODE_SESSION", 3);
define("PERSISTENCE_MODE_COOKIE", 4);
define("PERSISTENCE_MODE_ENCRYPTED", 5);
define("PERSISTENCE_MODE_INTERSECTION", 6);
define("PERSISTENCE_MODE_EMBEDDED", 7); // stored in a separate table; physically separates columns installed by different modules
define("PERSISTENCE_MODE_ALIAS", 8);

define("SERVER_TYPE_DATABASE", "database");
define("SERVER_TYPE_WEB", "web");
define("SERVER_TYPE_BACKUP", "backup");
define("SERVER_TYPE_HOTWALLET", "wallet");
define("SERVER_TYPE_EMAIL", "mail");
define("SERVER_TYPE_MONOLITHIC", "monolithic");

define("KEYGEN_STATE_UNINITIALIZED", 0);
define("KEYGEN_STATE_GENERATED", 1);

define("LOAD_ENTRY_POINT_UNDEFINED", 0);
define("LOAD_ENTRY_POINT_SELF", 1);
define("LOAD_ENTRY_POINT_INTERSECTION", 2);
define("LOAD_ENTRY_POINT_DEFAULT", LOAD_ENTRY_POINT_SELF);

define("DATA_MODE_UNDEFINED", 0); // error
define("DATA_MODE_DEFAULT", 1); // datum behaves normally
define("DATA_MODE_RECEPTIVE", 2); // datum will generate values for other linked data in its encryption scheme
define("DATA_MODE_SEALED", 3); // datum does not accept new values
define("DATA_MODE_PASSIVE", 4); // datum accepts new values, but will not automatically encrypt an encrypted value

define("DATA_STRUCTURE_RELATION_UNDEFINED", 0);
define("DATA_STRUCTURE_RELATION_DEFAULT", 1);
define("DATA_STRUCTURE_RELATION_SUBORDINATE", 2);
define("DATA_STRUCTURE_RELATION_ASSOC", 3); // data structure is an associative array

define("REMOVAL_REASON_UNDEFINED", 0);
define("REMOVAL_REASON_TEST", 1);
define("REMOVAL_REASON_COPYRIGHT_DMCA", 2);
define("REMOVAL_REASON_CHILD_ABUSE_BLATANT", 3);
define("REMOVAL_REASON_CHILD_ABUSE_SUSPECTED", 4);
define("REMOVAL_REASON_HATE_SPEECH", 5);
define("REMOVAL_REASON_FAKE_NEWS", 6);
define("REMOVAL_REASON_MALWARE", 7);
define("REMOVAL_REASON_PHISHING", 8);
define("REMOVAL_REASON_SPAM", 9);
define("REMOVAL_REASON_TERRORISM", 10);
define("REMOVAL_REASON_FATWA", 11);
define("REMOVAL_REASON_UR_A_CUNT", 12);
define("REMOVAL_REASON_ANIMAL_ABUSE", 13);
define("REMOVAL_REASON_COPYRIGHT_PROACTIVE", 14);
define("REMOVAL_REASON_SHITPOSTING", 15);
define("REMOVAL_REASON_FRAUD", 16);
define("REMOVAL_REASON_EXPIRED", 17);
define("REMOVAL_REASON_DELETED", 18);
define("REMOVAL_REASON_PRIVATE", 19);
define("REMOVAL_REASON_CLASSIFIED", 20);

define("MEDIA_COMMAND_UNDEFINED", 0);
define("MEDIA_COMMAND_INSERT_BEFORE", 1); // insert an element before another
define("MEDIA_COMMAND_INSERT_AFTER", 2); // insert an element after another
define("MEDIA_COMMAND_APPEND_CHILD", 3); // append a child node to a parent's child node list
define("MEDIA_COMMAND_REPLACE", 4); // replace an element's innerHTML, ID and/or class
define("MEDIA_COMMAND_DELETE", 5); // delete a node
define("MEDIA_COMMAND_FADE", 6); // like delete but the element gets faded and shrunk down first
define("MEDIA_COMMAND_CLEAR", 7); // clear an input element

define("ADDRESS_TYPE_CLEARTEXT", "cleartext");
define("ADDRESS_TYPE_SHADOW", "shadow");
define("ADDRESS_TYPE_SECURE", "secure");
define("ADDRESS_TYPE_TEMP", "temp");

// select statement lock options
define("LOCK_OPTION_NOWAIT", "nowait");
define("LOCK_OPTION_SKIP_LOCKED", "skip locked");

// account lock options
define("LOCK_OPTION_LOCK", "lock");
define("LOCK_OPTION_UNLOCK", "unlock");

// table lock options
define("LOCK_OPTION_DEFAULT", CONST_DEFAULT);
define("LOCK_OPTION_NONE", CONST_NONE);
define("LOCK_OPTION_SHARED", "shared");
define("LOCK_OPTION_EXCLUSIVE", "exclusive");

define("LOCK_IN_SHARE_MODE", "lock in share mode");
define("LOCK_FOR_UPDATE", "update");
define("LOCK_FOR_SHARE", "share");

define("STATEMENT_TYPE_UNDEFINED", 0);
define("STATEMENT_TYPE_CREATE_USER", 1);
define("STATEMENT_TYPE_ALTER_USER", 2);
define("STATEMENT_TYPE_ALTER_CURRENT_USER", 3);

define("DATABASE_OBJECT_TYPE_FUNCTION", "function");
define("DATABASE_OBJECT_TYPE_PROCEDURE", "procedure");
define("DATABASE_OBJECT_TYPE_TABLE", "table");

define("CASE_SNAKE", 1);
define("CASE_CAMEL", 2);
define("CASE_PASCAL", 3);
define("CASE_TITLE", 4);
define("CASE_KEBAB", 5);

define("DISTINCTION_ALL", "all");
define("DISTINCTION_DISTINCT", "distinct");
define("DISTINCTION_DISTINCTROW", "distinctrow");

define("SEX_FEMALE", "female");
define("SEX_MALE", "male");
define("SEX_INTERSEX", "intersex");
define("SEX_ROBOT", "robot");

define("SEVERITY_NONE", CONST_NONE);
define("SEVERITY_MILD", "mild");
define("SEVERITY_MODERATE", "moderate");
define("SEVERITY_SEVERE", "severe");

define("IMAGE_TYPE_GENERIC", "generic");
define("IMAGE_TYPE_ENCRYPTED_ATTACHMENT", "encrypted");
define("IMAGE_TYPE_SLIDE", "slide");
define("IMAGE_TYPE_PROFILE", "profile");
define("IMAGE_TYPE_KYC", "kyc");
define("IMAGE_TYPE_PRODUCT", "product");
define("IMAGE_TYPE_FEATURE", "feature");
define("IMAGE_TYPE_EMBDEDDED", "embed");
define("IMAGE_TYPE_UNDFINED", CONST_UNDEFINED);

define("THUMBNAIL_MAX_DIMENSION", 100);

define("ASYNC_REQUEST_METHOD_NONE", 0);
define("ASYNC_REQUEST_METHOD_XHR", 1);
define("ASYNC_REQUEST_METHOD_FETCH", 2);

// define("ELEMENT_INNERHTML_/CONVERSION_UNDEFINED", 0);
// define("ELEMENT_INNERHTML_/CONVERSION_ASSOC", 1); //return innerHTML as associative array
// define("ELEMENT_INNERHTML_/CONVERSION_STRING", 2); //return innerHTML as string
// define("ELEMENT_INNERHTML_/CONVERSION_LOCALFILE", 3); //return innerHTML as filename of local file
// define("ELEMENT_INNERHTML_/CONVERSION_NONE", 4); //element has no innerHTML

define("INPUT_PLACEHOLDER_MODE_UNDEFINED", 0);
define("INPUT_PLACEHOLDER_MODE_NORMAL", 1);
define("INPUT_PLACEHOLDER_MODE_SHRINK", 2);

define("COUNTERPART_ROLE_UNDEFINED", 0);
define("COUNTERPART_ROLE_INSTIGATOR", 1);
define("COUNTERPART_ROLE_PASSIVE", 2);

define("COVALIDATE_UNDEFINED", CONST_UNDEFINED);
define("COVALIDATE_BEFORE", CONST_BEFORE);
define("COVALIDATE_AFTER", CONST_AFTER);

define("ALLOCATION_MODE_UNDEFINED", 0); // default behavior
define("ALLOCATION_MODE_EAGER", 1); // generate child nodes immediately
define("ALLOCATION_MODE_LAZY", 2); // defer child node generation until element needs them to be rendered or converted to array
define("ALLOCATION_MODE_NEVER", 3); // do not generate child nodes
define("ALLOCATION_MODE_FORM", 4); // generate inputs only for a form that is getting processed but not rendered or converted to array
define("ALLOCATION_MODE_TEMPLATE", 5); // for generating a javascript function that creates the element client side
define("ALLOCATION_MODE_EMAIL", 6); // for elements getting rendered as part of inline HTML email. This has implications mostly for attached images.
define("ALLOCATION_MODE_DOMPDF_COMPATIBLE", 7); // element is to be rendered by dompdf, which does not support many element tags
define("ALLOCATION_MODE_ULTRA_LAZY", 8); // immediately echo appended child nodes as JSON or HTML and deallocate
define("ALLOCATION_MODE_SUBJECTIVE", 9);
define("ALLOCATION_MODE_FORM_TEMPLATE", 10);
define("ALLOCATION_MODE_DEBUG", 11);

define("ARRAY_MEMBERSHIP_CONFIG_BACKUP", "backup");
define("ARRAY_MEMBERSHIP_CONFIG_DEFAULT", CONST_DEFAULT);

define("KEY_GENERATION_MODE_UNIDENTIFIABLE", 0);
define("KEY_GENERATION_MODE_NATURAL", 1);
define("KEY_GENERATION_MODE_PSEUDOKEY", 2);
define("KEY_GENERATION_MODE_LITERAL", 3);
define("KEY_GENERATION_MODE_HASH", 4);

define("AVATAR_TYPE_UNDEFINED", 0);
define("AVATAR_TYPE_NOTIFICATION", 1);
define("AVATAR_TYPE_CONVERSATION", 2);
define("AVATAR_TYPE_PREVIEW", 3);
define("AVATAR_TYPE_TEMPLATE", 4);

define("READABILITY_UNDEFINED", 0);
define("READABILITY_READABLE", 1);
define("READABILITY_WRITABLE", 2);

define("SEARCH_MODE_UNDEFINED", 0);
define("SEARCH_MODE_ANY", "any");
define("SEARCH_MODE_ALL", "all");
define("SEARCH_MODE_EXACT", "exact");

define("EFFECT_NONE", CONST_NONE);
define("EFFECT_FADE", "fade");

define("FOREIGN_UPDATE_BEHAVIOR_UNDEFINED", 0);
define("FOREIGN_UPDATE_BEHAVIOR_NORMAL", 1);
define("FOREIGN_UPDATE_BEHAVIOR_DELETE", 2);

define("EVENT_APOPTOSE", "apoptose"); // fired in DataStructure->apoptose
define("EVENT_BEFORE_AUTHENTICATE", "before_authenticate");
define("EVENT_AFTER_AUTHENTICATE", "after_authenticate");
define("EVENT_BEFORE_CONSTRUCTOR", "before_construct");
define("EVENT_AFTER_CONSTRUCTOR", "after_construct");
define("EVENT_BEFORE_CREATE_TABLE", "before_create"); // fired in DataStructure->createTable
define("EVENT_AFTER_CREATE_TABLE", "after_create");
define("EVENT_BEFORE_DELETE", "before_delete"); // fired in DataStructure->generateKey
define("EVENT_AFTER_DELETE", "after_delete");
define("EVENT_BEFORE_DELETE_FOREIGN", "before_delete_foreign");
define("EVENT_AFTER_DELETE_FOREIGN", "after_delete_foreign");
define("EVENT_BEFORE_DERIVE", "before_derive");
define("EVENT_AFTER_DERIVE", "after_derive");
define("EVENT_BEFORE_EJECT", "before_eject"); // fired in Datum->ejectValue
define("EVENT_AFTER_EJECT", "after_eject");
define("EVENT_BEFORE_EXECUTE", "before_execute"); // fired in UseCase->execute
define("EVENT_AFTER_EXECUTE", "after_execute");
define("EVENT_BEFORE_EXPAND", "before_expand");
define("EVENT_AFTER_EXPAND", "after_expand");
define("EVENT_BEFORE_RENDER", "before_render"); // fired in Command, Element & XMLHttpResponse->generateContents
define("EVENT_AFTER_RENDER", "after_render");
define("EVENT_BEFORE_GENERATE_KEY", "before_generate_key"); // fired in DataStructure->generateKey
define("EVENT_AFTER_GENERATE_KEY", "after_generate_key");
define("EVENT_BEFORE_INSERT", "before_insert"); // fired in DataStructure->insert
define("EVENT_AFTER_INSERT", "after_insert");
define("EVENT_BEFORE_INSERT_FOREIGN", "before_insert_foreign"); // fired in DataStructure->insertForeignDataStructures
define("EVENT_AFTER_INSERT_FOREIGN", "after_insert_foreign");
define("EVENT_BEFORE_LOAD", "before_load"); // fired in DataStructure->load
define("EVENT_AFTER_LOAD", "after_load");
define("EVENT_LOAD_FAILED", "load_failed"); // fired in DataStructure->loadFailure
define("EVENT_BEFORE_EDIT", "before_edit"); // fired in DataStructure->beforeEditHook
define("EVENT_AFTER_EDIT", "after_edit"); // fired in DataStructure->afterEditHook
define("EVENT_BEFORE_REPLICATE", "before_replicate"); // fired in Datum->replicate and DataStructure->replicate
define("EVENT_AFTER_REPLICATE", "after_replicate");
define("EVENT_BEFORE_RESPOND", "before_respond"); // fired in StandardWorkflow->respond
define("EVENT_AFTER_RESPOND", "after_respond");
define("EVENT_BEFORE_SAVE", "before_save"); // fired in DataStructure->beforeSaveHook
define("EVENT_AFTER_SAVE", "after_save"); // fired in DataStructure->afterSaveHook
define("EVENT_BEFORE_SET_FOREIGN", "before_set_foreign"); // fired in DataStructure->setForeignDataStructure
define("EVENT_AFTER_SET_FOREIGN", "after_set_foreign");
define("EVENT_BEFORE_SET_VALUE", "before_set_value"); // fired in Datum->setValue
define("EVENT_AFTER_SET_VALUE", "after_set_value");
define("EVENT_BEFORE_SUBINDEX", "before_subindex"); // fired in InputElement->subindexNameAttribute
define("EVENT_AFTER_SUBINDEX", "after_subindex");
define("EVENT_SUBMIT_FORM", "submit");
define("EVENT_BEFORE_UNSET_VALUE", "before_unset_value"); // fired in Datum->unsetValue()
define("EVENT_AFTER_UNSET_VALUE", "after_unset_value");
define("EVENT_BEFORE_UPDATE", "before_update"); // fired in DataStructure->update
define("EVENT_AFTER_UPDATE", "after_update");
define("EVENT_BEFORE_UPDATE_FOREIGN", "before_update_foreign"); // fired in DataStructure->updateForeignDataStructures
define("EVENT_AFTER_UPDATE_FOREIGN", "after_update_foreign");
define("EVENT_USE_CASE_TRANSITION", "transition"); // fired in UseCase->execute
define("EVENT_NONE", CONST_NONE); // never happens

define("RELATIONSHIP_TYPE_UNDEFINED", 0);
define("RELATIONSHIP_TYPE_ONE_TO_ONE", 1);
define("RELATIONSHIP_TYPE_ONE_TO_MANY", 2);
define("RELATIONSHIP_TYPE_MANY_TO_ONE", 3);
define("RELATIONSHIP_TYPE_MANY_TO_MANY", 4);

define("FILE_GENERATION_MODE_ERROR", 0);
define("FILE_GENERATION_MODE_LOCAL", 1);
define("FILE_GENERATION_MODE_DOMPDF", 2);

define("QUOTE_STYLE_SINGLE", "'");
define("QUOTE_STYLE_DOUBLE", '"');
define("QUOTE_STYLE_BACKTICK", '`');

define("ESCAPE_TYPE_FUNCTION", "function");
define("ESCAPE_TYPE_STRING", "string");
define("ESCAPE_TYPE_PARENTHESIS", "()");
define("ESCAPE_TYPE_OBJECT", "{}");
define("ESCAPE_TYPE_NONE", CONST_NONE);
define("ESCAPE_TYPE_AUTO", CONST_AUTO);

define("ACTIVITY_ACTIVE", "active");
define("ACTIVITY_INACTIVE", "inactive");

define("SEARCH_MODIFIER_NATURAL_LANGUAGE_MODE", "in natural language mode");
define("SEARCH_MODIFIER_NATURAL_LANGUAGE_QUERY_EXPANSION", "in natural language mode with query expansion");
define("SEARCH_MODIFIER_BOOLEAN_MODE", "in boolean mode");
define("SEARCH_MODIFIER_QUERY_EXPANSION", "with query expansion");

// define("THEME_DEFAULT", CONST_DEFAULT);
// define("THEME_DARK", "dark");
// define("THEME_LIGHT", "light");

// these are used for multiple features that concern similar phases of an object/use case lifecycle
// e.g. flags, permissions, button input names, directive
define("DIRECTIVE_NONE", 'none');
define("DIRECTIVE_ADD", 'add');
define("DIRECTIVE_ADMIN_LOGIN", "admin_login");
define("DIRECTIVE_ADMIN_MFA", "admin_mfa");
define("DIRECTIVE_REPLICATE", 'replicate');
define("DIRECTIVE_CREATE_TABLE", 'create');
define("DIRECTIVE_DELETE", 'delete');
define("DIRECTIVE_DELETE_FOREIGN", 'deleteForeign');
define("DIRECTIVE_DOWNLOAD", "download");
define("DIRECTIVE_DROP", 'drop');
define("DIRECTIVE_EMAIL_CONFIRMATION_CODE", 'email_code');
define("DIRECTIVE_ERROR", "error");
define("DIRECTIVE_FORGOT_CREDENTIALS", 'reset');
define("DIRECTIVE_IGNORE", 'ignore');
define("DIRECTIVE_IMPORT_CSV", 'importCSV');
define("DIRECTIVE_INSERT", 'insert');
define("DIRECTIVE_INSERT_BEFORE", 'insertBefore'); // insert a node before another node in a graph
define("DIRECTIVE_INSERT_AFTER", 'insertAfter');
define("DIRECTIVE_UNSHIFT_CHILD", 'unshiftChild');
define("DIRECTIVE_APPEND_CHILD", 'appendChild');
define("DIRECTIVE_PREINSERT_FOREIGN", 'preinsertForeign');
// define("DIRECTIVE_INSERT_FOREIGN", "insertForeign");
define("DIRECTIVE_POSTINSERT_FOREIGN", 'postinsertForeign');
define("DIRECTIVE_LANGUAGE", 'language');
define("DIRECTIVE_LOGIN", 'login');
define("DIRECTIVE_LOGOUT", 'logout');
define("DIRECTIVE_MASS_DELETE", 'mass_delete');
define("DIRECTIVE_MFA", 'login_mfa');
define("DIRECTIVE_PROCESS", 'process');
define("DIRECTIVE_PREVIEW", 'preview');
define("DIRECTIVE_READ", 'read');
define("DIRECTIVE_READ_MULTIPLE", 'read_multiple');
define("DIRECTIVE_REFRESH_SESSION", 'refresh_session');
define("DIRECTIVE_REGENERATE", 'regenerate');
define("DIRECTIVE_REPLACE", 'replace');
define("DIRECTIVE_SEARCH", 'search');
define("DIRECTIVE_SUBMIT", 'submit');
define("DIRECTIVE_TRANSITION_FROM", 'transitionFrom');
define("DIRECTIVE_TRANSITION_TO", 'transitionTo');
define("DIRECTIVE_UNSET", 'unset');
define("DIRECTIVE_UPDATE", 'update');
define("DIRECTIVE_PREUPDATE_FOREIGN", 'preupdateForeign');
// define("DIRECTIVE_UPDATE_FOREIGN", 'updateForeign');
define("DIRECTIVE_POSTUPDATE_FOREIGN", 'postupdateForeign');
define("DIRECTIVE_SELECT", 'select');
define("DIRECTIVE_UPLOAD", 'upload');
define("DIRECTIVE_VALIDATE", 'validate');
define("DIRECTIVE_VIEW", 'view');

define("FOREIGN_RELATIONSHIP_TYPE_SHARED", "shared");
define("FOREIGN_RELATIONSHIP_TYPE_SUBORDINATE", "subordinate");
define("FOREIGN_RELATIONSHIP_TYPE_SUPERIOR", "superior");
define("FOREIGN_RELATIONSHIP_TYPE_UNDEFINED", CONST_UNDEFINED);

define("ALGORITHM_COPY", "copy");
define("ALGORITHM_DEFAULT", CONST_DEFAULT);
define("ALGORITHM_INPLACE", "inplace");
define("ALGORITHM_INSTANT", "instant");
define("ALGORITHM_MERGE", "merge");
define("ALGORITHM_TEMPTABLE", "temptable");
define("ALGORITHM_UNDEFINED", "undefined");

define("PARTITION_TYPE_HASH", "hash");
define("PARTITION_TYPE_KEY", "key");
define("PARTITION_TYPE_LIST", "list");
define("PARTITION_TYPE_RANGE", "range");

define("COLUMN_POSITION_FIRST", "first");
define("COLUMN_POSITION_AFTER", "after");

define("INDEX_TYPE_BTREE", "btree");
define("INDEX_TYPE_HASH", "hash");
define("INDEX_TYPE_FULLTEXT", "fulltext");
define("INDEX_TYPE_SPATIAL", "spatial");

define("VISIBILITY_VISIBLE", "visible");
define("VISIBILITY_INVISIBLE", "invisible");

define("COMPRESSION_TYPE_NONE", CONST_NONE);
define("COMPRESSION_TYPE_LZ4", 'lz4');
define("COMPRESSION_TYPE_ZLIB", 'zlib');

define("INSERT_METHOD_FIRST", "first");
define("INSERT_METHOD_LAST", "last");
define("INSERT_METHOD_NO", "no");

define("ROW_FORMAT_DEFAULT", CONST_DEFAULT);
define("ROW_FORMAT_DYNAMIC", "dynamic");
define("ROW_FORMAT_FIXED", "fixed");
define("ROW_FORMAT_COMPRESSED", "compressed");
define("ROW_FORMAT_REDUNDANT", "redundant");
define("ROW_FORMAT_COMPACT", "compact");

define("COLUMN_FORMAT_DEFAULT", CONST_DEFAULT);
define("COLUMN_FORMAT_DYNAMIC", "dynamic");
define("COLUMN_FORMAT_FIXED", "fixed");

define("NULLITY_NULL", "null");
define("NULLITY_NOT_NULL", "not null");

define("DATABASE_STORAGE_DISK", "disk");
define("DATABASE_STORAGE_MEMORY", "memory");
define("DATABASE_STORAGE_GENERATED_VIRTUAL", "virtual");
define("DATABASE_STORAGE_GENERATED_STORED", "stored");

define("REFERENCE_OPTION_RESTRICT", "restrict");
define("REFERENCE_OPTION_CASCADE", "cascade");
define("REFERENCE_OPTION_SET_NULL", "set null");
define("REFERENCE_OPTION_NO_ACTION", "no_action");
define("REFERENCE_OPTION_SET_DEFAULT", "set_default");

define("MATCH FULL", "match_full");
define("MATCH PARTIAL", "match_partial");
define("MATCH SIMPLE", "match_simple");

define("JOIN_TYPE_JOIN", "join");
define("JOIN_TYPE_INNER", "inner");
define("JOIN_TYPE_CROSS", "cross");
define("JOIN_TYPE_STRAIGHT", "straight_join");
define("JOIN_TYPE_LEFT", "left");
define("JOIN_TYPE_RIGHT", "right");
define("JOIN_TYPE_NATURAL", "natural");
define("JOIN_TYPE_NATURAL_INNER", "natural inner");
define("JOIN_TYPE_NATURAL_LEFT", "natural left");
define("JOIN_TYPE_NATURAL_RIGHT", "natural right");

define("INDEX_HINT_TYPE_USE", "use");
define("INDEX_HINT_TYPE_IGNORE", "ignore");
define("INDEX_HINT_TYPE_FORCE", "force");

define("INDEX_HINT_FOR_JOIN", "join");
define("INDEX_HINT_FOR_ORDER_BY", "order by");
define("INDEX_HINT_FOR_GROUP_BY", "group by");

// different forms of InsertStatement
define("INSERT_STATEMENT_FORM_UNDEFINED", 0);
define("INSERT_STATEMENT_FORM_VALUES", 1);
define("INSERT_STATEMENT_FORM_VALUES_ROW", 2);
define("INSERT_STATEMENT_FORM_SET", 3);
define("INSERT_STATEMENT_FORM_SELECT", 4);
define("INSERT_STATEMENT_FORM_TABLE", 5);

define("PRIORITY_DELAYED", "delayed");
define("PRIORITY_HIGH", "high_priority");
define("PRIORITY_LOW", "low_priority");
define("PRIORITY_CONCURRENT", "concurrent");

define("PASSWORD_OPTION_NEVER", "never");
define("PASSWORD_OPTION_OPTIONAL", "optional");
define("PASSWORD_OPTION_UNBOUNDED", "unbounded");

define("STORAGE_ENGINE_INNODB", "InnoDB");
define("STORAGE_ENGINE_MEMORY", "MEMORY");
define("STORAGE_ENGINE_MYISAM", "MyISAM");
define("STORAGE_ENGINE_NDB", "NDB");
define("STORAGE_ENGINE_HEAP", STORAGE_ENGINE_MEMORY);

define("SQL_SECURITY_DEFINER", "definer");
define("SQL_SECURITY_INVOKER", "invoker");

define("CHECK_OPTION_CHECK", "check");
define("CHECK_OPTION_CASCADED", "cascaded");
define("CHECK_OPTION_LOCAL", "local");

define("TYPE_UNKNOWN", CONST_UNKNOWN);
define("TYPE_ARRAY", "array");
define("TYPE_STRING", "string");

define("COMPUTER_LANGUAGE_SQL", "sql");
define("COMPUTER_LANGUAGE_JAVASCRIPT", "js");
define("COMPUTER_LANGUAGE_HTML", "html");
define("COMPUTER_LANGUAGE_CSS", "css");
define("COMPUTER_LANGUAGE_BASH", "bash");

define("ROUTINE_TYPE_FUNCTION", "function");
define("ROUTINE_TYPE_PROCEDURE", "procedure");
define("ROUTINE_TYPE_STATIC", "static");

define("SCOPE_TYPE_CONST", "const");
define("SCOPE_TYPE_LET", "let");
define("SCOPE_TYPE_VAR", "var");

define("DIRECTION_ASCENDING", "asc");
define("DIRECTION_DESCENDING", "desc");

define("STRING_TYPE_HARDCODED", "hardcoded");
define("STRING_TYPE_NAME", "name");
define("STRING_TYPE_SHARED", "shared");
define("STRING_TYPE_DELETED", "deleted");

define("IP_ADDRESS_TYPE_LISTED", "listed");
define("IP_ADDRESS_TYPE_CONDEMNED", "condemned");

define("FILE_TYPE_ENCRYPTED", "crypt");

define("ORIENTATION_SQUARE", "square");
define("ORIENTATION_LANDSCAPE", "landscape");
define("ORIENTATION_PORTRAIT", "portrait");

define("COLUMN_FILTER_ADD_TO_RESPONSE", "addToResponse");
define("COLUMN_FILTER_AFTER", CONST_AFTER);
define("COLUMN_FILTER_ALIAS", "alias");
define("COLUMN_FILTER_ALPHANUMERIC", "alphanum");
define("COLUMN_FILTER_ALWAYS_VALID", 'alwaysValid');
define("COLUMN_FILTER_APOPTOTIC", "apoptotic");
define("COLUMN_FILTER_ARRAY_MEMBER", "member");
define("COLUMN_FILTER_MEMBER", COLUMN_FILTER_ARRAY_MEMBER);
define("COLUMN_FILTER_AUTO_INCREMENT", "autoInc");
define("COLUMN_FILTER_AUTOLOAD", "autoload");
define("COLUMN_FILTER_BBCODE", "bb");
define("COLUMN_FILTER_BEFORE", CONST_BEFORE);
define("COLUMN_FILTER_BOOLEAN", "b");
define("COLUMN_FILTER_COMPARABLE", "compare");
define("COLUMN_FILTER_CONSTRAIN", "constrain");
define("COLUMN_FILTER_CONTRACT_VERTEX", "contract");
define("COLUMN_FILTER_COOKIE", "cookie");
define("COLUMN_FILTER_CREATE_TABLE", DIRECTIVE_CREATE_TABLE);
define("COLUMN_FILTER_DATABASE", "db");
define("COLUMN_FILTER_DECLARED", "decl");
define("COLUMN_FILTER_DEFAULT", CONST_DEFAULT);
define("COLUMN_FILTER_DIRTY_CACHE", "dirtyCache");
define("COLUMN_FILTER_DISABLED", "disable");
define("COLUMN_FILTER_DOUBLE", "d");
define("COLUMN_FILTER_EAGER", "eager");
define("COLUMN_FILTER_EMBEDDED", "embed");
define("COLUMN_FILTER_ENCRYPTED", "crypt");
define("COLUMN_FILTER_EVENT_SOURCE", "ev");
define("COLUMN_FILTER_FLOAT", "f");
define("COLUMN_FILTER_FOREIGN", "foreign");
define("COLUMN_FILTER_FULLTEXT", "fulltext");
define("COLUMN_FILTER_ID", "id");
define("COLUMN_FILTER_INDEX", "index");
define("COLUMN_FILTER_INSERT", DIRECTIVE_INSERT);
define("COLUMN_FILTER_INTEGER", "i");
define("COLUMN_FILTER_INTERSECTION", "intersection");
define("COLUMN_FILTER_LOADED", "loaded");
define("COLUMN_FILTER_NL2BR", "nl2br");
define("COLUMN_FILTER_NOW", "now");
define("COLUMN_FILTER_NULLABLE", "nul");
define("COLUMN_FILTER_ONE_SIDED", "1side");
define("COLUMN_FILTER_ORIGINAL", "orig");
define("COLUMN_FILTER_POTENTIAL", "potential");
define("COLUMN_FILTER_PREVENT_CIRCULAR_REF", "preventCircRef");
define("COLUMN_FILTER_PRIMARY_KEY", "primary");
define("COLUMN_FILTER_RECURSIVE_DELETE", "recursiveDelete");
define("COLUMN_FILTER_REPLICA", "replica");
define("COLUMN_FILTER_RETAIN_ORIGINAL_VALUE", "retain");
define("COLUMN_FILTER_REWRITABLE", "rw");
define("COLUMN_FILTER_SEALED", "seal");
define("COLUMN_FILTER_SEARCHABLE", "search");
define("COLUMN_FILTER_SENSITIVE", "sensitive");
define("COLUMN_FILTER_SERIAL", "serial");
define("COLUMN_FILTER_SESSION", "session");
define("COLUMN_FILTER_SIGNED", "sign");
define("COLUMN_FILTER_SORTABLE", "sort");
define("COLUMN_FILTER_STRING", "s");
define("COLUMN_FILTER_TEMPLATE", "template");
define("COLUMN_FILTER_TIMESTAMP", "time");
define("COLUMN_FILTER_TRIMMABLE", "trim");
define("COLUMN_FILTER_UNIQUE", "unique");
define("COLUMN_FILTER_UNSIGNED", "unsign");
define("COLUMN_FILTER_UPDATE", DIRECTIVE_UPDATE);
define("COLUMN_FILTER_VALUED", "val");
define("COLUMN_FILTER_VIRTUAL", "virtual");
define("COLUMN_FILTER_VOLATILE", "volatile");

define("PARAMETER_DATATYPE", "dataType");

define("APP_INTEGRATION_MODE_STANDALONE", "standalone");
define("APP_INTEGRATION_MODE_UNIVERSAL", "universal");

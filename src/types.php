<?php
namespace JulianSeymour\PHPWebApplicationFramework;

define("DATATYPE_UNKNOWN", "unknown"); // used for objects that don't get written to the database
define("DATATYPE_CASCADE_DELETE", "cascadeDelete");
define("DATATYPE_USER", "user");
define("DATATYPE_IP_ADDRESS", "ipAddress");
define("DATATYPE_ENCRYPTED_DATABASE_CREDENTIALS", "databaseCredentials"); // password for a mysql user that only the admin needs (e.g. for adding new services)
define("DATATYPE_USED_OTP", "usedOTP"); // InvalidatedOtp
define("DATATYPE_CONFIRMATION_CODE", "confirmationCode"); // pre-emptive record for email confirmation codes
define("DATATYPE_NOTIFICATION", "notification"); // data used to mirror push and on-site notifications
define("DATATYPE_FILE", "file"); // encrypted file metadata including path and keys
define("DATATYPE_PUSH_SUBSCRIPTION", "pushSubscription");
define("DATATYPE_SEARCH_QUERY", "searchQuery");
define("DATATYPE_LINKCOUNTER", "throttleMeter"); // stores custom thresholds for throttling end users' access to database
define("DATATYPE_1ST_PARTY_SERVER_KEYPAIR", "1stPartyServer"); // public key for communicating between web and backup servers
define("DATATYPE_ADDRESS", "address");
define("DATATYPE_STRING_MULTILINGUAL", "multilingualString");
define("DATATYPE_STRING_TRANSLATED", "string");
define("DATATYPE_SEARCH_FIELDS", "searchFields");
define("DATATYPE_INTERSECTION", "intersection");
define("DATATYPE_DATABASE_USER_ROLE", "databaseUserOrRole");
define("DATATYPE_GEOLOCATION_POSITION", "geolocationPosition");
define("DATATYPE_EMBEDDED", "embedded");
define("DATATYPE_EVENT_SOURCE", "eventSource");
define("DATATYPE_GROUP", "group");
define("DATATYPE_ROLE_DECLARATION", "roleDeclaration");
define("DATATYPE_PERMISSION", "permission");
define("DATATYPE_ACCESS_CONTROL_LIST", "accessControlList");
define("DATATYPE_GRANT", "grant");
define("DATATYPE_CHANNEL", "channel");
define("DATATYPE_GROUP_INVITE", "groupInvite");
define("DATATYPE_EMAIL_RECORD", "emailRecord");
define("DATATYPE_CATEGORY", "category");
define("DATATYPE_USERNAME", "username");
define("DATATYPE_ACCESS_ATTEMPT", "access");
define("DATATYPE_SESSION_RECOVERY", "sessionRecovery");
define("DATATYPE_EMAIL_NOTIFICATION", "emailNotification");
define("DATATYPE_NONEXISTENT_URI", "nonexistentUri");
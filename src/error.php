<?php
namespace JulianSeymour\PHPWebApplicationFramework;

define("FAILURE", 0);
define("SUCCESS", 1);

// 100 Continue
// 101 Switching protocols
// 102 Processing
// 103 Early hints
define("STATUS_OK", 200); // 200 OK
                          // 201 Created
                          // 202 Accepted
                          // 203 Non-authoritative information
                          // 204 No content
                          // 205 Reset content
define("STATUS_PARTIAL_CONTENT", 206); // 206 Partial content
                                       // 207 Multi-status (WebDAV)
                                       // 208 Already reported (WebDAV)
                                       // 226 IM used (HTTP Delta encoding)
                                       // 300 Multiple choices
                                       // 301 Moved permanently
                                       // 302 Found
                                       // 303 See other
                                       // 304 Not modified
                                       // 305 Use proxy (deprecated)
                                       // 306 unused, reserved
                                       // 307 Temporary redirect
                                       // 308 Permanent redirect
define("ERROR_BAD_REQUEST", 400); // 400 Bad request
define("ERROR_UNAUTHORIZED", 401); // 401 Unauthorized (i.e. no credentials or malformed credentials)
define("ERROR_PAYMENT_REQUIRED", 402); // 402 Payment required (reserved for future use)
define("ERROR_FORBIDDEN", 403); // 403 Forbidden (i.e. insufficient but valid credentials)
define("ERROR_FILE_NOT_FOUND", 404); // 404 File not found
define("ERROR_METHOD_NOT_ALLOWED", 405); // 405 Method not allowed
define("ERROR_NOT_ACCEPTABLE", 406); // 406 Not acceptable
define("ERROR_PROXY_AUTHENTICATION", 407); // 407 Proxy authentication
define("ERROR_REQUEST_TIMEOUT", 408); // 408 Request timeout
define("ERROR_CONFLICT", 409); // 409 Conflict
define("ERROR_GONE", 410); // 410 Gone
define("ERROR_LENGTH_REQUIRED", 411); // 411 Length required
define("ERROR_PRECONDITION_FAILED", 412); // 412 Precondition failed
define("ERROR_PAYLOAD_TOO_LARGE", 413); // 413 Payload too large
define("ERROR_URI_TOO_LONG", 414); // 414 URI too long
define("ERROR_UNSUPPORTED_MEDIA_TYPE", 415); // 415 Unsupported media type
define("ERROR_RANGE_NOT_SATISFIABLE", 416); // 416 Range not satisfiable
define("ERROR_EXPECTATION_FAILED", 417); // 417 Expectation failed
                                         // 418 I'm a teapot
define("ERROR_MISDIRECTED_REQUEST", 421); // 421 Misdirected request
define("ERROR_UNPROCESSABLE_ENTITY", 422); // 422 Unprocessable entity (WebDAV)
define("ERROR_LOCKED", 423); // 423 Locked (WebDAV)
define("ERROR_FAILED_DEPENDENCY", 424); // 424 Failed dependency (WebDAV)
define("ERROR_TOO_EARLY", 425); // 425 Too early
define("ERROR_UPGRADE_REQUIRED", 426); // 426 Upgrade required
define("ERROR_PRECONDITION_REQUIRED", 428); // 428 Precondition required
define("ERROR_TOO_MANY_REQUESTS", 429); // 429 Too many requests
define("ERROR_REQUEST_HEADER_FIELDS_TOO_LARGE", 431); // 431 Request header fields too large
define("ERROR_UNAVAILABLE_LEGAL", 451); // 451 Unavailable for legal reasons
define("ERROR_INTERNAL", 500); // 500 Internal server error
define("ERROR_NOT_IMPLEMENTED", 501); // 501 Not implemented
define("ERROR_BAD_GATEWAY", 502); // 502 Bad gateway
define("ERROR_SERVICE_UNAVAILABLE", 503); // 503 Service unavailable
define("ERROR_GATEWAY_TIMEOUT", 504); // 504 Gateway timeout
define("ERROR_HTTP_VERSION_UNSUPPORTED", 505); // 505 HTTP version not supported
define("ERROR_VARIANT_ALSO_NEGOTIATES", 506); // 506 Variant also negotiates
define("ERROR_INSUFFICIENT_STORAGE", 507); // 507 Insufficient storage (WebDAV)
define("ERROR_LOOP_DETECTED", 508); // 508 Loop detected (WebDAV)
define("ERROR_NOT_EXTENDED", 510); // 510 Not extended
define("ERROR_NETWORK_AUTHENTICATION_REQUIRED", 511); // 511 Network authentication required

define("ERROR_TYPE", 600);
define("ERROR_PAYPAL_UNDEFINED", 601);
define("ERROR_ARTICLE_CONTENT", 602);
define("ERROR_BASE_SVCKEY", 603);
define("ERROR_0_TRANSACTED_SERVICES", 604);
define("ERROR_NAME_UNDEFINED", 605);
define("ERROR_NULL_REFLECTED_CLASS", 606);
define("ERROR_IDNUM_UNDEFINED", 607);
define("ERROR_KEY_UNDEFINED", 608);
define("ERROR_SEARCHNAME_UNDEFINED", 609);
// define("ERROR_NULL_COUNTERPARTKEY", 610);

define("ERROR_SIGNATURE_FAILED", 611); // sodium_crypto_sign_verify_detatched returned false
define("ERROR_CONVERSATION_TYPE", 612);
define("ERROR_NULL_KEYPAIR", 613);
define("ERROR_NOTE_UNCHANGED", 614);
define("ERROR_UPDATE_KEYMAP", 615);
define("ERROR_NULL_SERVICE", 616);
define("ERROR_NULL_BASE_SERVICE_OBJECT", 618);
define("ERROR_NULL_MANUFACTURER", 619);
define("ERROR_NULL_CHILD_CLASS", 620);
define("ERROR_NULL_TRANSACTION_ID", 621);
define("ERROR_NULL_PARENT", 622);
define("STATUS_DELETED", 623);
define("ERROR_DELETED", STATUS_DELETED);
define("ERROR_BASESVCMAP_UNINITIALIZED", 624);
define("ERROR_NULL_TEMPLATE", 625);
define("RESULT_DELETE_SESSIONS_SUCCESS", 626);

define("ERROR_NULL_OBJECT", 628);
define("RESULT_NOTE_UPDATED", 629);
define("ERROR_INTEGER_OOB", 630);
define("ERROR_NULL_STRING", 631);
define("ERROR_NULL_CORRESPONDENT_OBJECT", 632);
define("ERROR_CHILD_STATE", 633);
define("ERROR_ALREADY_INITIALIZED", 634);
define("ERROR_ALREADY_EXPANDED", 635);
define("ERROR_NULL_CHILDREN", 636);
define("ERROR_NULL_FAMILY", 637);
define("ERROR_NULL_MSGBOX", 638);
define("ERROR_NULL_MESSAGE_ID", 639);
define("ERROR_NULL_MSGCIPHER", 640);
define("ERROR_NULL_KEYCIPHER", 641);
define("ERROR_NULL_NONCE", 642);
define("ERROR_NULL_ADMINKEYCIPHER", 643);
define("ERROR_SODIUM_KEYSIZE", 644);
define("ERROR_SODIUM_PRIVATEKEYSIZE", 645);
define("ERROR_SODIUM_KEYPAIRSIZE", 646);
define("ERROR_NULL_MSGCLEARTEXT", 647);
define("ERROR_INVALID_CONTEXT", 648);
define("ERROR_TABLE_EXISTS_NONEMPTY", 649);
define("ERROR_NULL_FORMID", 650);
define("ERROR_MUST_LOGIN", 651);
define("ERROR_NULL_TABLE_VAR", 652);
define("ERROR_IMPLICITSVC_ALREADY_MAPPED", 653);
define("ERROR_PROCESS_AVAILABLE", 654);
define("ERROR_NULL_PARENT_WRAPPER", 655);
define("ERROR_NULL_BACKUP", 656);
define("ERROR_IDENTICAL_NAME", 657);
define("ERROR_NULL_PARENT_KEY", 658);
define("ERROR_IDENTICAL_PARENTKEY", 659);
define("ERROR_IDENTICAL_KEY", 660);
define("ERROR_NULL_DESCRIPTION", 661);
define("ERROR_NULL_DESCRIPTION_KEY", 662);
define("RESULT_UPDATE_NEW_DESCRIPTION", 663);
define("ERROR_MULTIPLE_ACTIONS", 664);
define("ERROR_NULL_TEMPLATE_KEY", 665);
define("ERROR_NULL_QUERY", 666);
define("ERROR_NOT_MAPPED", 667);
define("ERROR_NOT_EXPANDED", 668);
define("ERROR_NAME_LENGTH", 669);
define("ERROR_REGISTER_NULL_CONFIRM", 670);
define("ERROR_PAYPAL_EXPRESSCHECKOUTPAYMENT", 671);
define("ERROR_BINDPARAM_BOUNDARY", 672);
define("ERROR_REGISTER_PASSWORD_DATA", 673);
define("ERROR_NULL_TARGET_OBJECT", 674);
define("WARNING_REFILL_SUCCESS_EXPIRED", 675);
define("RESULT_DELETE_FAILED_IN_USE", 676);
define("ERROR_NULL_MSGOBJ", 677);
define("ERROR_USER_NOT_FOUND", 678);
define("ERROR_ACTIVATE_ALREADY_LOGGED_IN", 679); // XXX forgot to implement this -- when the user attempts to activate an account while logged in as a different one (they would have to simply visit the confirmation url)
define("ERROR_NULL_MAGNITUDE", 681);
define("RESULT_REFILL_SUCCESS_SESSION_QUANTITY", 682);
define("ERROR_NULL_ITERATOR", 683);
define("ERROR_NULL_TARGET_KEY", 684);
define("ERROR_NULL_PRODUCT_KEY", 685);
define("ERROR_NULL_PREDECESSOR", 686);
define("ERROR_PRICE_0CREDITS", 687);
define("STATUS_CHECKOUT_VERIFY", 688);
define("ERROR_BINDPARAM_PRIMITIVES", 689);
define("ERROR_CREDITS_PAID", 690);
define("RESULT_PARTIAL_PAYMENT", 691);
define("ERROR_NULL_TRANSACTION", 692);
define("ERROR_KEY_INHERITED", 693);
define("STATUS_INVALIDATED_SVC_ORPHAN", 694);
define("RESULT_TEMPLATE_UPDATE", 695);
define("RESULT_TEMPLATE_INSERT", 696);
define("ERROR_ALREADY_DELETED", 697);
define("ERROR_EXPANSION_DIRECTION", 698);
define("ERROR_PARENT_TYPE", 699);
define("ERROR_NULL_LOGIN_RESULT", 700);
define("ERROR_NULL_IMPLICIT_SVC", 701);
define("ERROR_NULL_SERVICE_KEY", 702);
define("ERROR_NULL_IMPLICIT_SVC_OPT", 703);
define("ERROR_NULL_OPTION_TRANSACTION", 704);
define("ERROR_ORDER_POST_KEY", 705);
define("ERROR_TRANSACTION_STATE", 706);
define("RESULT_TRANSACTION_UPDATED", 707);
define("ERROR_OVERPAID", 708);
define("ERROR_NULL_USER_OBJECT", 709);
define("ERROR_NULL_ORDER_OBJECT", 710);
define("ERROR_NULL_ORDER_KEY", 711);
define("ERROR_NULL_USER_KEY", 712);
define("ERROR_NULL_IDENTIFIER", 713);
define("ERROR_UPDATE_TIMESTAMP", 714);
define("ERROR_NULL_IP_ADDRESS", 715);
define("ERROR_IMEI_NOCHANGE", 716);
define("ERROR_NETWORKCODE", 717);
define("ERROR_MIME_TYPE", 718);
define("RESULT_SETTINGS_UPDATED", 719);
define("ERROR_NULL_TYPE", 720);
define("RESULT_ACTIVATE_SUCCESS", 721);
define("RESULT_BFP_BANNED_IP", 722);
define("RESULT_BFP_IP_LOCKOUT_START", 723);
define("RESULT_BFP_IP_LOCKOUT_CONTINUED", 724);
define("RESULT_BFP_USERNAME_LOCKOUT_START", 725);
define("RESULT_BFP_USERNAME_LOCKOUT_CONTINUED", 726);
define("RESULT_BFP_MFA_CONFIRM", 727);
define("RESULT_BFP_MFA_FAILED", 728);
define("ERROR_NULL_OBJECTNUM", 729);
define("ERROR_FILE_PARAMETERS", 730);
define("ERROR_MFA_CONFIRM", 731);
define("RESULT_MFA_ENABLED", 732);
define("RESULT_MFA_DISABLED", 733);
define("RESULT_MFA_GENERATED", 734);
define("RESULT_MFA_DISPLAY", 735);
define("RESULT_REGISTER_FAILED", 736);
define("RESULT_BFP_REGISTRATION_LOCKOUT", 737);
define("RESULT_BFP_CAPTCHA_FAILED", 738);
define("RESULT_BFP_WAIVER_FAILED_MULTIPLE", 739);
define("RESULT_BFP_WAIVER_FAILED_IP_RECENT", 740);
define("RESULT_ORDER_SUCCESS", 741);
define("INFO_LOGIN_TO_ACTIVATE", 742);
define("ERROR_NULL_CHILD_INDEX", 743);
define("RESULT_RESENT_ACTIVATION", 744);
define("STATUS_NO_NEWMSG", 745);
define("STATUS_NEWMSG", 746);
define("ERROR_ALREADY_LOGGED", 747);
define("RESULT_LOGGED_OUT", 748);
define("ERROR_NULL_STATE", 749);
define("ERROR_WRONG_MESSAGE_BOX", 750);
define("ERROR_NULL_AES_KEY", 751);
define("ERROR_NULL_PRODUCT", 752);
define("ERROR_NULL_PARENT_TYPE", 753);
define("ERROR_NULL_PARENT_CLASS", 754);
define("ERROR_NULL_CORRESPONDENT_KEY", 755);
define("ERROR_PAYPAL_HOLD_PAYMENTHOLD", 756);
define("ERROR_PAYPAL_HOLD_NEWSELLER", 757);
define("ERROR_PAYPAL_HOLD_UNDOCUMENTED", 758);
define("ERROR_PAYPAL_REVERSAL_NONE", 759);
define("ERROR_PAYPAL_REVERSAL_CHARGEBACK", 760);
define("ERROR_PAYPAL_REVERSAL_GUARANTEE", 761);
define("ERROR_PAYPAL_REVERSAL_COMPLAINT", 762);
define("ERROR_PAYPAL_REVERSAL_REFUND", 763);
define("ERROR_PAYPAL_REVERSAL_OTHER", 764);
define("ERROR_PAYPAL_REVERSAL_UNDOCUMENTED", 765);
define("ERROR_PAYPAL_PENDING_NONE", 766);
define("ERROR_PAYPAL_PENDING_ADDRESS", 767);
define("ERROR_PAYPAL_PENDING_AUTHORIZATION", 768);
define("ERROR_PAYPAL_PENDING_ECHECK", 769);
define("ERROR_PAYPAL_PENDING_INTL", 770);
define("ERROR_PAYPAL_PENDING_MULTICURRENCY", 771);
define("ERROR_PAYPAL_PENDING_ORDER", 772);
define("ERROR_PAYPAL_PENDING_PAYMENTREVIEW", 773);
define("ERROR_PAYPAL_PENDING_REGULATORY", 774);
define("ERROR_PAYPAL_PENDING_UNILATERAL", 775);
define("ERROR_PAYPAL_PENDING_VERIFY", 776);
define("ERROR_PAYPAL_PENDING_OTHER", 777);
define("ERROR_PAYPAL_PENDING_UNDOCUMENTED", 778);
define("ERROR_PAYPAL_STATUS_NONE", 779);
define("ERROR_PAYPAL_STATUS_CANCELED", 780);
define("ERROR_PAYPAL_STATUS_DENIED", 781);
define("ERROR_PAYPAL_STATUS_EXPIRED", 782);
define("ERROR_PAYPAL_STATUS_FAILED", 783);
define("ERROR_PAYPAL_STATUS_INPROGRESS", 784);
define("ERROR_PAYPAL_STATUS_PARTIALLYREFUNDED", 785);
define("ERROR_PAYPAL_STATUS_REFUNDED", 786);
define("ERROR_PAYPAL_STATUS_PROCESSED", 787);
define("ERROR_PAYPAL_STATUS_VOIDED", 788);
define("ERROR_PAYPAL_STATUS_UNDOCUMENTED", 789);
define("ERROR_NULL_PAYERID", 790);
define("ERROR_PAYPAL_DOPAYMENT", 791);
define("ERROR_HTTP_RESPONSE_LENGTH0", 792);
define("ERROR_NULL_CORRELATIONID", 793);
define("ERROR_UPLOAD_NO_FILE", 794);
define("ERROR_FILE_SIZE", 795);
define("ERROR_UNLINK", 796);
define("ERROR_FILE_PUT_CONTENTS", 797);
define("ERROR_HONEYPOT", 798);
define("ERROR_NULL_NOTIFICATION_OBJECT", 799);
define("ERROR_NULL_CONFIRMATION_CODE", 800);
define("ERROR_NULL_DURATION", 801);
define("ERROR_REQUIRED_ONLOAD", 802);
define("ERROR_NEVER_USE", 803);
define("ERROR_TEMPLATE_NOT_FOUND_GET_KEY", 804);
define("ERROR_ADMIN_ONLY", 805);
define("RESULT_NEWS_DELETED", 806);
define("ERROR_HCAPTCHA", 807);
define("RESULT_BFP_WAIVER_FAILED_RECORD_EXPIRED", 808);
define("RESULT_REGISTER_SUCCESS", SUCCESS);
define("ERROR_FORM_DATA_INDICES", 810);
define("STATUS_INITIALIZATION_IN_PROGRESS", 811);
define("ERROR_ALREADY_WAIVED", 812);
define("ERROR_CONFIRMATION_CODE_USED", 813);
define("ERROR_NULL_XSRF_TOKEN", 814);
define("ERROR_BLOCKED_IP_ADDRESS", 815);
define("ERROR_NULL_TRUE_USER_OBJECT", 816);
define("RESULT_BFP_WHITELIST_UNAUTHORIZED", 817);
define("ERROR_FILTER_LOCKED_OUT", 818);
define("ERROR_IPv6_UNSUPPORTED", 819);
define("RESULT_CIDR_UNMATCHED", 820);
define("ERROR_KEY_COLLISION", 821);
define("RESULT_IP_AUTHORIZED", 822);
define("RESULT_IP_BANNED", 823);
define("STATUS_UNCHANGED", 824);
define("ERROR_NULL_REQUEST_ATTEMPT", 825);
define("RESULT_CODE_VALIDATED", 826);
define("ERROR_SERVER_ALREADY_INITIALIZED", 827);
define("ERROR_INVALID_SERVER_NAME", 828);
define("ERROR_ARRAY_ENCODING", 829);
define("ERROR_SERVER_UNINITIALIZED", 830);
define("ERROR_NO_BACKUP_SERVER", 831);
define("ERROR_WRONG_SERVER", 832);
define("ERROR_PLZLOGIN_MESSENGER", 833);
define("ERROR_SESSION_EXPIRED", 834);
define("ERROR_NULL_USECASE", 835);
define("ERROR_NULL_MODEL_OBJECT", 836);
define("RESULT_CHECKOUT_SUCCESS", SUCCESS);

define("ERROR_ADMIN_FORBIDDEN", 838);
define("ERROR_DEPRECATED", 839);
define("ERROR_NULL_USERNAME", 840);
define("ERROR_NULL_PASSWORD_HASH", 841);
define("STATUS_NOTHING_HERE_YET", 842);
define("ERROR_NULL_CREDIT_SIGN", 844);
define("ERROR_INVALID_CURRENCY", 845);
define("ERROR_SODIUM_PUBLICKEYSIZE", 846);
define("ERROR_INVOICE_FORM_EMPTY", 847);
define("RESULT_BFP_RETRY_LOGIN", 848);
define("ERROR_FORGOT_PASSWORD_DISABLED", 849);
define("ERROR_FORGOT_USERNAME_DISABLED", 850);
define("ERROR_NULL_SIGNATORY", 851);
define("ERROR_MESSAGE_FLOOD", 852);
define("ERROR_ATTACHMENTS_DISABLED", 853);
define("ERROR_USER_ROLE", 854);
define("ERROR_LOCATION_KEY", 855);
define("ERROR_APPOINTMENT_TYPE", 856);
define("ERROR_COMMENT_UNAUTHORIZED", 857);
define("RESULT_SUBMISSION_ACCEPTED", 858);
define("ERROR_EDIT_ACCESS_DENIED", 859);
define("RESULT_EDIT_COMMENT_SUCCESS", 860);
define("ERROR_EXPIRED_PUSH_SUBSCRIPTION", 861);
define("ERROR_NULL_PERMISSION", 862);
define("ERROR_ANONYMOUS_REQUIRED", 863);
define("ERROR_USER_TYPE", 864);
define("ERROR_NULL_PICKUP_LOCATION_KEY", 865);
define("ERROR_NULL_DELIVERY_LOCATION_KEY", 866);
define("ERROR_NO_ADDRESSES", 867);
define("ERROR_PREDECESSOR_CLASS", 868);
define("ERROR_PAYMENT_FAILED", 869);
define("RESULT_MANUAL_PAYMENT_SUBMITTED", 870);
define("ERROR_REJECTION_REASON_REQUIRED", 871);
define("ERROR_NULL_COUNTERPART_OBJECT", 872);
define("ERROR_TEMPLATE_NOT_FOUND", 873);
define("ERROR_INVALID_EMAIL_ADDRESS", 874);
define("ERROR_INVALID_USERNAME", 875);
define("ERROR_AGREE_TOS", 876);
define("RESULT_DELETE_DATA_STRUCTURE", 877);
define("STATUS_PRELAZYLOAD", 878);
define("STATUS_DISPLAY_PROMPT", 879);
define("RESULT_SENT_EMAIL", 880);
define("ERROR_INVALID_IP_ADDRESS", 881);
define("STATUS_SKIP_ME", 882);
define("ERROR_NULL_CORRESPONDENT_TYPE", 883);
define("ERROR_INVALID_PASSWORD", 884);
define("ERROR_NULL_MFA_SEED", 885);
define("ERROR_REPLACEMENT_KEY_REQUESTED", 886);
define("ERROR_0_SEARCH_RESULTS", 887);
define("ERROR_NULL_SEARCH_QUERY", 888);
define("RESULT_DELETED", STATUS_DELETED);
define("ERROR_CROSS_ORIGIN_REQUEST", 889);
define("ERROR_USER_NOT_FOUND_GET_EMAIL", 890);
define("ERROR_NULL_SIGNATURE", 891);
define("ERROR_CANNOT_DISMISS", 892);
define("ERROR_NOTIFICATION_TYPE", 893);
define("ERROR_CHECKOUT_IMEI_TAMPER", 894);
define("ERROR_CHECKOUT_AUTO_REJECT_ACCESS", 895);
define("ERROR_CHECKOUT_TIER_UNDEFINED", 896);
define("ERROR_NULL_PUBLIC_SIGNATURE_KEY", 897);
define("ERROR_NULL_QUANTITY", 898);
define("ERROR_NULL_TOKEN", 899);
define("RESULT_INITIALIZED", 900);
define("STATUS_READY_WRITE", 901);
define("STATUS_UNINITIALIZED", 902);
define("ERROR_UNHANDLED_EXCEPTION", 903);
define("ERROR_MYSQL_QUERY", 904);
define("ERROR_MYSQL_RESULT", 905);
define("ERROR_XSRF", 906);
define("ERROR_DISPATCH_NOTHING", 907); // superglobal parameters missing
define("ERROR_MYSQL_EXECUTE", 908);
define("ERROR_MYSQL_BIND", 909);
define("ERROR_MYSQL_PREPARE", 910);
define("ERROR_MYSQL_CONNECT", 911);
define("RESULT_LOGIN_FAILURE_CONTINUE", 912);
define("ERROR_REGISTER_EMAIL_USED", 913);
define("ERROR_REGISTER_EMAIL_INVALID", 914);
define("ERROR_REGISTER_NAME_USED", 915);
define("ERROR_PASSWORD_WEAK", 916);
define("ERROR_PASSWORD_MISMATCH", 917);
define("ERROR_REGISTER_AGREE_TOS", 918);
define("ERROR_LINK_EXPIRED", 919);
define("ERROR_DEADBEAT", 920);
define("ERROR_CHECKOUT_REPEAT", 921);
define("WARNING_NEED_CONFIRM", 922);
define("ERROR_INSERT_FAILURE_BLANK", 923);
define("ERROR_WRONG_CORRESPONDENT", 924);
define("ERROR_MESSAGE_ACCESS", 925);
define("ERROR_PARENT_NOT_FOUND", 926);
define("ERROR_CIRCULAR_INHERITANCE", 927);
define("STATUS_HASKEY_NOTLOADED", 928);
define("ERROR_INPUT_BLANK", 929);
define("ERROR_LOGIN_CREDENTIALS", 930);
define("ERROR_ACCOUNT_INACTIVE", 932);
define("ERROR_ACCOUNT_DISABLED", 933);
define("ERROR_ALREADY_ACTIVE", 934);
define("ERROR_PASSWORD_LOGGED", 936); // password is incorrect for an already logged in user (used e.g. to reveal MFA codes)
define("ERROR_NULL_PLAINTEXT_NONCE", 937);
define("ERROR_NULL_REAUTH_CIPHER", 938);
define("ERROR_DUPLICATE_ENTRY", 939);
define("ERROR_KEY_CHANGED", 940);
define("ERROR_IMPOSSIBLE_VALUE", 941);
define("ERROR_NULL_PLAINTEXT_CIPHER", 942);
define("ERROR_NULL_CHILD_OBJECT", 943);
define("ERROR_MYSQLI_FETCH", 944);
define("ERROR_NULL_TIMESTAMP", 945);
define("ERROR_LOGIN_LOCKOUT", 946);
define("ERROR_LOGIN_XSRF", ERROR_XSRF);
define("ERROR_TEMPLATE_TABLE", 947);
define("ERROR_PASSWORD_UNDEFINED", 948);
define("ERROR_CAUGHT_EXCEPTION", 949);
define("RESULT_BFP_WAIVER_SUCCESS", 950);
define("ERROR_CHECKOUT_EMPTY", 951);
define("RESULT_CHECKOUT_CONFIRM", 952);
define("ERROR_CHECKOUT_IDK_QTY", 953);
define("ERROR_CHECKOUT_MODE", 954);
define("ERROR_CHECKOUT_IMEI_SINGLE", 955);
define("RESULT_ALREADY_CREDITED", 956);
define("ERROR_TAMPER_POST", 957);
define("ERROR_IMEI_INVALID", 958);
define("ERROR_IMEI_REQUIRED", 959);
define("ERROR_CHECKOUT_IMEI_DUPLICATE", 960);
define("ERROR_JAVASCRIPT_REQUIRED", 961);
define("ERROR_USER_NOT_FOUND_POST_KEY", 962);
define("ERROR_SENDMAIL", 963);
define("ERROR_RECOVERY_NOKEY", 964);
define("ERROR_PASSWORDS_UNDEFINED", 965);
define("RESULT_EDITUSER_SUCCESS_POSTKEY", 966);
define("ERROR_ASSIGN_DELETED_TEMPLATE", 967);
define("ERROR_NULL_TARGET_CLASS", 968);
define("ERROR_NOT_FOUND", 969);
define("ERROR_CONFIRMATION_CODE_UNDEFINED", 970);
define("RESULT_RESET_SUCCESS", 971);
define("RESULT_RESET_SUBMIT", 972);
define("RESULT_CHANGEPASS_SUCCESS", 973);
define("ERROR_ADMIN_CREDENTIALS", 974);
define("ERROR_NULL_SERVICE_CLASS", 975);
define("ERROR_NULL_PASSWORD_DATA", 976);
define("ERROR_CHANGEMAIL_NOCODE", 978);
define("RESULT_CHANGEMAIL_SUBMIT", 979);
define("RESULT_CHANGEMAIL_SUCCESS", 980);
define("RESULT_LANGUAGE_SETTINGS_UPDATED", 981);
define("ERROR_NULL_EMAIL", 982);
define("ERROR_CHANGEMAIL_MISMATCH", 983); // new email and new email confirmation don't match
define("ERROR_CHANGEMAIL_BADCODE", 984); // confirmation code differs from what was calculated
define("ERROR_IP_ADDRESS_BLOCKED_BY_USER", 985);
define("ERROR_IP_ADDRESS_NOT_AUTHORIZED", 986);
define("ERROR_DECRYPTION_FAILED", 987);

define("RESULT_READY_WRITE", STATUS_READY_WRITE);
define("ERROR_MESSAGE_CONNECT", ERROR_MYSQL_CONNECT);
define("ERROR_UNDEFINED", FAILURE);
define("ERROR_MESSAGE_UNKNOWN", ERROR_UNDEFINED);
define("ERROR_LOGIN_GENERIC", ERROR_UNDEFINED);
define("ERROR_MISSING_VARIABLE", ERROR_UNDEFINED);
define("ERROR_YOUR_FAULT", ERROR_UNDEFINED);
define("ERROR_REGISTER_GENERIC", ERROR_UNDEFINED);
define("ERROR_LOGIN_PASSWORD_EMPTY", ERROR_PASSWORD_UNDEFINED);
define("ERROR_REGISTER_PASSWORD_UNDEFINED", ERROR_PASSWORD_UNDEFINED);
define("ERROR_LOGIN_PASSWORD_UNDEFINED", ERROR_PASSWORD_UNDEFINED);
define("ERROR_CHANGEPASS_MISSING", ERROR_PASSWORD_UNDEFINED);
define("ERROR_CHANGEPASS_FAILURE", FAILURE);
define("ERROR_RECOVERY_GENERIC", ERROR_CHANGEPASS_FAILURE);
define("ERROR_RESET_FAILURE", ERROR_CHANGEPASS_FAILURE);
define("ERROR_CHANGEPASS_MISMATCH", ERROR_PASSWORD_MISMATCH);
define("ERROR_NULL_PROTECT", ERROR_NULL_TOKEN);
define("ERROR_RESET_DELETION", ERROR_LINK_EXPIRED);
define("ERROR_RESET_GENERIC", ERROR_UNDEFINED);
define("ERROR_CHANGEMAIL_GENERIC", ERROR_UNDEFINED);
define("ERROR_EMAIL_UNDEFINED", ERROR_NULL_EMAIL);
define("ERROR_CHANGEMAIL_EMPTY", ERROR_EMAIL_UNDEFINED);
define("ERROR_RECOVERY_0ROWS", ERROR_NOT_FOUND); // link expired
define("ERROR_MYSQL_NO_RESULTS", ERROR_NOT_FOUND);
define("ERROR_RECOVERY_PASSWORDS", ERROR_PASSWORDS_UNDEFINED);
define("ERROR_RECOVERY_MISMATCH", ERROR_PASSWORD_MISMATCH);
define("ERROR_CHECKOUT_TAMPER", ERROR_TAMPER_POST);
define("ERROR_CHECKOUT_IMEI_INVALID", ERROR_IMEI_INVALID);
define("ERROR_CHECKOUT_IMEI_REQUIRED", ERROR_IMEI_REQUIRED);
define("ERROR_RECOVERY_UNDEFINED", ERROR_UNDEFINED);
define("ERROR_RECOVERY_SENDMAIL", ERROR_SENDMAIL);
define("ERROR_RECOVERY_XSRF", ERROR_XSRF);
define("ERROR_MESSAGE_BOX", ERROR_WRONG_MESSAGE_BOX);
define("ERROR_NULL_PARENTCLASS", ERROR_NULL_PARENT_CLASS);
define("ERROR_NULL_REQUEST_RECORD", ERROR_NULL_CONFIRMATION_CODE);
define("ERROR_EMPLOYEES_ONLY", ERROR_ADMIN_ONLY);
define("ERROR_NULL_REQUEST ATTEMPT", ERROR_NULL_REQUEST_ATTEMPT);
define("ERROR_USERNAME_UNDEFINED", ERROR_NULL_USERNAME);
define("STATUS_NOTHING_SUBMAT", STATUS_NOTHING_HERE_YET);
define("STATUS_INSERT_SUCCESSFUL", RESULT_SUBMISSION_ACCEPTED);
define("ERROR_INVALID_MFA_OTP", RESULT_BFP_MFA_FAILED);
define("ERROR_UNINITIALIZED", STATUS_UNINITIALIZED);
define("ERROR_REGISTER_PASSWORD_WEAK", ERROR_PASSWORD_WEAK);
define("ERROR_REGISTER_PASSWORD_NOTMATCH", ERROR_PASSWORD_MISMATCH);
define("ERROR_CHECKOUT_GENERIC", FAILURE);
define("ERROR_BADCODE", ERROR_LINK_EXPIRED);
define("ERROR_CHANGEPASS_BADCODE", ERROR_BADCODE);
define("ERROR_RECOVERY_BADCODE", ERROR_BADCODE);
define("ERROR_CHECKOUT_DEADBEAT", ERROR_DEADBEAT);
define("ERROR_TIMESTAMP_EXPIRED", ERROR_LINK_EXPIRED);
define("ERROR_CONFIRMATION_URL", ERROR_LINK_EXPIRED);
define("ERROR_MULTIPLE_ROWS", ERROR_DUPLICATE_ENTRY);
define("ERROR_CHANGEMAIL_ROWCOUNT", ERROR_MULTIPLE_ROWS);
define("ERROR_PAYPAL_FAILED", FAILURE);
define("ERROR_NULL_ADOPT", ERROR_NULL_CHILD_OBJECT);
define("ERROR_MYSQLI_RESULT_UNDEFINED", ERROR_MYSQLI_FETCH);
define("ERROR_MESSAGE_TYPE", ERROR_TYPE);
define("ERROR_PROCESS_DATATYPE", ERROR_TYPE);
define("ERROR_PROCESS_TYPE", ERROR_TYPE);
define("ERROR_PAYPAL_GENERIC", ERROR_PAYPAL_UNDEFINED);
define("ERROR_BASE_SVCOBJ", ERROR_NULL_BASE_SERVICE_OBJECT);
define("ERROR_NULL_CHILDCLASS", ERROR_NULL_CHILD_CLASS);
define("ERROR_NULL_KEY", ERROR_KEY_UNDEFINED);
define("ERROR_NULL_REFLECTED_CLIENT", ERROR_NULL_CORRESPONDENT_OBJECT);
define("ERROR_DISPATCH_OBJECTS", ERROR_NULL_CHILDREN);
define("ERROR_NULL_MSGID", ERROR_NULL_MESSAGE_ID);
define("ERROR_NULL_MSGKEYCIPHER", ERROR_NULL_KEYCIPHER);
define("ERROR_NULL_MSGNONCE", ERROR_NULL_NONCE);
define("ERROR_RESET_KEYSIZE", ERROR_SODIUM_KEYSIZE);
define("ERROR_NULL_CONTEXT", ERROR_INVALID_CONTEXT);
define("ERROR_NULL_CHILD_STATE", ERROR_CHILD_STATE);
define("ERROR_NULL_PARENTKEY", ERROR_NULL_PARENT_KEY);
define("ERROR_PARENTKEY", ERROR_NULL_PARENT_KEY);
define("ERROR_NAME_IDENTICAL", ERROR_IDENTICAL_NAME);
define("ERROR_KEY_IDENTICAL", ERROR_IDENTICAL_KEY);
define("ERROR_KEY_UNCHANGED", ERROR_IDENTICAL_KEY);
define("ERROR_UPDATE_NULL_DESCRIPTION", ERROR_NULL_DESCRIPTION);
define("ERROR_REPORT_NONSTRING", ERROR_TYPE);
define("ERROR_NULL_SERVICE_TRANSACTION", ERROR_NULL_TRANSACTION);
define("RESULT_TEMPLATE_DELETE", STATUS_DELETED);
define("RESULT_DELETE_SUCCESS", STATUS_DELETED);
define("RESULT_DELETION_SUCCESSFUL", RESULT_DELETE_SUCCESS);
define("ERROR_TEMPLATE_PARENT_TYPE", ERROR_PARENT_TYPE);
define("ERROR_SERVICE_BUNDLE", ERROR_NULL_ORDER_OBJECT);
define("ERROR_NULL_SVCBUNDLEKEY", ERROR_NULL_ORDER_KEY);
define("RESULT_GETORDERNOTE", RESULT_NOTE_UPDATED);
define("RESULT_CHANGESETTINGS_SUCCESS", RESULT_SETTINGS_UPDATED);
define("ERROR_NULL_PROTECTEDTYPE", ERROR_NULL_TYPE);
define("RESULT_BFP_USERNAME_NO_EXISTE", ERROR_USER_NOT_FOUND);
define("ERROR_NULL_USERKEY", ERROR_NULL_USER_KEY);

define("RECOURSE_ABORT", 0);
define("RECOURSE_RETRY", 1);
define("RECOURSE_IGNORE", 2);
define("RECOURSE_EXIT", 3);
define("RECOURSE_CONTINUE", 4);

define("IMAGE_ERROR_URI", "rca.png");


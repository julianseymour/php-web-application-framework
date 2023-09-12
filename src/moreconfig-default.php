<?php

namespace JulianSeymour\PHPWebApplicationFramework;

if(! defined("ULTRA_LAZY")) {
	define("ULTRA_LAZY", true);
}
if(! defined("CACHE_ENABLED")) {
	define("CACHE_ENABLED", true);
}
if(! defined("FILE_CACHE_ENABLED")) {
	define("FILE_CACHE_ENABLED", CACHE_ENABLED && true);
}
if(! defined("HTML_CACHE_ENABLED")) {
	define("HTML_CACHE_ENABLED", FILE_CACHE_ENABLED && true);
}
if(! defined("QUERY_CACHE_ENABLED")) {
	define("QUERY_CACHE_ENABLED", CACHE_ENABLED && true);
}
if(! defined("JAVASCRIPT_CACHE_ENABLED")) {
	define("JAVASCRIPT_CACHE_ENABLED", FILE_CACHE_ENABLED && true);
}
if(! defined("CSS_CACHE_ENABLED")) {
	define("CSS_CACHE_ENABLED", FILE_CACHE_ENABLED && true);
}
if(! defined("JSON_CACHE_ENABLED")) {
	define("JSON_CACHE_ENABLED", FILE_CACHE_ENABLED && true);
}
if(! defined("USER_CACHE_ENABLED")) {
	define("USER_CACHE_ENABLED", CACHE_ENABLED && true);
}
if(! defined("CUSTOM_ROLE_PREFIX")) {
	define("CUSTOM_ROLE_PREFIX", "}%");
}
if(! defined("SECURE_FILE_PRIV")) {
	define("SECURE_FILE_PRIV", "/var/lib/mysql-files/");
}
if(! defined("UNDECLARED_FLAGS_ENABLED")) {
	define("UNDECLARED_FLAGS_ENABLED", true);
}
if(! defined("MESSAGE_LIMIT")) {
	define("MESSAGE_LIMIT", 50);
}
if(! defined("MAX_FAILED_LOGINS_BY_NAME")) {
	define("MAX_FAILED_LOGINS_BY_NAME", 5);
}
if(! defined("MAX_FAILED_LOGINS_BY_IP")) {
	define("MAX_FAILED_LOGINS_BY_IP", 6);
}
if(! defined("FILE_SIZE_LIMIT")) {
	define("FILE_SIZE_LIMIT", 5000000);
}
if(! defined("MFA_OTP_LENGTH")) {
	define("MFA_OTP_LENGTH", 6);
}
if(! defined("MFA_KEYGEN_INTERVAL")) {
	define("MFA_KEYGEN_INTERVAL", 30);
}
if(! defined("MINIMUM_PASSWORD_LENGTH")) {
	define("MINIMUM_PASSWORD_LENGTH", 12);
}
if(! defined("IMAGE_MAX_DIMENSION")) {
	define("IMAGE_MAX_DIMENSION", 1920);
}
if(! defined("APPLICATION_INTEGRATION_MODE")) {
	define("APPLICATION_INTEGRATION_MODE", APP_INTEGRATION_MODE_UNIVERSAL);
}
if(! defined("SESSION_REGENERATION_INTERVAL")) {
	define("SESSION_REGENERATION_INTERVAL", 180);
}
if(!defined("LOCKOUT_DURATION")){
	define("LOCKOUT_DURATION", 6000);
}
if(!defined("SESSION_TIMEOUT_SECONDS")){
	define("SESSION_TIMEOUT_SECONDS", 1440);
}
if(!defined("MAX_FAILED_WAIVERS_BY_USER_KEY")){
	define("MAX_FAILED_WAIVERS_BY_USER_KEY", 5);
}
if(!defined("MAX_FAILED_WAIVERS_BY_IP")){
	define("MAX_FAILED_WAIVERS_BY_IP", 5);
}
if(!defined("IMAGE_ERROR_URI")){
	define("IMAGE_ERROR_URI", "rca.png");
}
if(!defined("CUSTOM_ROLE_PREFIX")){
	define("CUSTOM_ROLE_PREFIX", "}%");
}
if(!defined("SECURE_FILE_PRIV")){
	define("SECURE_FILE_PRIV", "/var/lib/mysql-files/");
}
if(!defined("ULTRA_LAZY")){
	define("ULTRA_LAZY", true);
}
if(!defined("CACHE_ENABLED")){
	define("CACHE_ENABLED", true);
}
if(!defined("FILE_CACHE_ENABLED")){
	define("FILE_CACHE_ENABLED", true);
}
if(!defined("HTML_CACHE_ENABLED")){
	define("HTML_CACHE_ENABLED", true);
}
if(!defined("QUERY_CACHE_ENABLED")){
	define("QUERY_CACHE_ENABLED", true);
}
if(!defined("JAVASCRIPT_CACHE_ENABLED")){
	define("JAVASCRIPT_CACHE_ENABLED", true);
}
if(!defined("CSS_CACHE_ENABLED")){
	define("CSS_CACHE_ENABLED", true);
}
if(!defined("JSON_CACHE_ENABLED")){
	define("JSON_CACHE_ENABLED", true);
}
if(!defined("USER_CACHE_ENABLED")){
	define("USER_CACHE_ENABLED", true);
}
if(!defined("UNDECLARED_FLAGS_ENABLED")){
	define("UNDECLARED_FLAGS_ENABLED", true);
}

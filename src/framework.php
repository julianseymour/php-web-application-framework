<?php

namespace JulianSeymour\PHPWebApplicationFramework;

use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\app\ApplicationConfiguration;
use JulianSeymour\PHPWebApplicationFramework\app\ApplicationRuntime;
use JulianSeymour\PHPWebApplicationFramework\app\ModuleBundler;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\cache\MultiCache;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Debugger;
use JulianSeymour\PHPWebApplicationFramework\data\IntersectionData;
use JulianSeymour\PHPWebApplicationFramework\db\DatabaseManager;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\load\LazyLoadHelper;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\notification\push\PushNotifier;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

function app(): ?ApplicationRuntime{
	global $__applicationInstance;
	return $__applicationInstance;
}

function cache(): MultiCache{
	return app()->getCache();
}

function comma_separate_sql($arr): ?string{
	if(empty($arr)) {
		return null;
	}
	$s = "";
	$count = 0;
	foreach($arr as $i) {
		if($count > 0) {
			$s .= ",";
		}
		$s .= $i->toSQL();
		$count ++;
	}
	return $s;
}

function config(): ApplicationConfiguration{
	return app()->getConfiguration();
}

function db(): DatabaseManager{
	return app()->getDatabaseManager();
}

function debug(): Debugger{
	return app()->getDebugger();
}

function directive(): ?string{
	return request()->getDirective();
}

function f(?string $class2 = null): string{
	$f = __FUNCTION__;
	if(! app()->getFlag("debug")) {
		return "";
	}
	$print = false;
	$bto = backtrace_omit(2, [
		"f"
	], true);
	if($print) {
		Debug::printArray($bto);
	}
	$class1 = array_key_exists("class", $bto) ? $bto['class'] : "";
	// $splat = explode('/', $bto['file']);
	// $class2 = explode('.', $splat[count($splat)-1])[0];
	$ret = $class1;
	if(is_string($class2) && class_exists($class1) && class_exists($class2) && is_a($class2, $class1, true)) {
		$ret .= "({$class2})";
	}
	if(!empty($class1)) {
		$ret .= "{$bto['type']}";
	}
	$ret .= "{$bto['function']}()";
	if($print) {
		Debug::print("{$f} returning \"{$ret}\"");
	}
	return $ret;
}

function generateSpecialTemplateKey(string $pc): string{
	return sha1("special_template-{$pc}");
}

function getCurrentUserAccountType(){
	return user()->getAccountType();
}

function getCurrentUserKey(){
	return user()->getIdentifierValue();
}

function getInputParameter(string $name, UseCase $use_case = null){
	return request()->getInputParameter($name, $use_case);
}

function getInputParameters(): ?array{
	return request()->getInputParameters();
}

function getRequestURISegment(int $i){
	return request()->getRequestURISegment($i);
}

function hasInputParameter(string $name, ?UseCase $use_case = null): bool{
	return request()->hasInputParameter($name, $use_case);
}

function hasInputParameters(...$names){
	$f = __FUNCTION__;
	if(empty($names)) {
		Debug::error("{$f} received empty parameter names");
	}
	$arr = [];
	foreach($names as $name) {
		array_push($arr, $name);
	}
	return request()->hasInputParameters(...$arr);
}

function intersectionalize($hostClass, $foreignClass, $foreignKeyName): int{
	$f = __FUNCTION__;
	$print = false;
	$intersection = new IntersectionData($hostClass, $foreignClass, $foreignKeyName);
	$db = $intersection->getDatabaseName();
	$table = $intersection->getTableName();
	$mysqli = db()->getConnection(PublicWriteCredentials::class);
	if(!$intersection->tableExists($mysqli)) {
		if($print) {
			Debug::print("{$f} about to create intersection table \"{$db}.{$table}\"");
		}
		$status = $intersection->createTable($mysqli);
		if($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} creating intersection table \"{$db}.{$table}\" returned error status \"{$err}\"");
		}
		return $status;
	}elseif($print) {
		Debug::printStackTraceNoExit("{$f} table \"{$db}.{$table}\" already exists");
	}
	return SUCCESS;
}

function lazy(): LazyLoadHelper{
	return app()->getLazyLoadHelper();
}

function mark(?string $where = '?'){
	$f = __FUNCTION__;
	$print = false;
	global $__clock;
	if(!$__clock) {
		return;
	}
	global $__mark;
	$time = microtime(true);
	$interval = $time - $__mark;
	if($print) {
		Debug::print("{$f} {$interval} seconds have passed {$where} since previous mark");
	}
	$GLOBALS['__mark'] = $time;
}

function mods(): ModuleBundler{
	return app()->getModuleBundler();
}

function push(): PushNotifier{
	return app()->getPushNotifier();
}

function registry(){
	return app()->getRegistry();
}

function request(): ?Request{
	return app()->getRequest();
}

function setInputParameter(string $name, $value){
	return request()->setInputParameter($name, $value);
}

function use_case(): ?UseCase{
	return app()->getUseCase();
}

function user(): ?PlayableUser{
	return app()->getUserData();
}

function x(string $f, Exception $x){
	Debug::error("{$f} exception: \"" . $x->__toString() . "\"");
}

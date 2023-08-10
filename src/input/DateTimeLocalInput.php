<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use function JulianSeymour\PHPWebApplicationFramework\getDateTimeStringFromTimestamp;

class DateTimeLocalInput extends DateInput implements StaticValueNegotiationInterface
{

	public function getTypeAttribute(): string
	{
		return "datetime-local";
	}

	public static function getTypeAttributeStatic(): string
	{
		return INPUT_TYPE_DATETIME_LOCAL;
	}

	public function getAllowEmptyInnerHTML()
	{
		return true;
	}

	public static function getHumanReadableValue($timestamp, $timezone)
	{
		return getDateTimeStringFromTimestamp($timestamp, $timezone);
	}

	/*
	 * public static function negotiateValueStatic(InputInterface $input, Datum $column){
	 * $f = __METHOD__; //DateTimeLocalInput::getShortClass()."(".static::getShortClass().")->negotiateValueStatic()";
	 * try{
	 * $print = false;
	 * $v = $input->getValueAttribute();
	 * if($column instanceof TimestampDatum){
	 * if($print){
	 * Debug::print("{$f} column is a TimestampDatum");
	 * }
	 * $datetimezone = new DateTimeZone(user()->getTimezone());
	 * $datetime = new DateTime($v, $datetimezone);
	 * $v = $datetime->getTimestamp();
	 * }else{
	 * Debug::error("{$f} column should always be a timestamp");
	 * }
	 * if($print){
	 * Debug::print("{$f} returning \"{$v}\"");
	 * }
	 * return $v;
	 * }catch(Exception $x){
	 * x($f, $x);
	 * }
	 * }
	 */

	/*
	 * public function negotiateValue(Datum $column){
	 * $f = __METHOD__; //DateTimeLocalInput::getShortClass()."(".static::getShortClass().")->negotiateValue()";
	 * try{
	 * if($this->hasNegotiator()){
	 * return parent::negotiateValue($column);
	 * }
	 * $print = false;
	 * $v = $this->getValueAttribute();
	 * if($column instanceof TimestampDatum){
	 * $user = user();
	 * $datetimezone = new DateTimeZone($user->getTimezone());
	 * //try{
	 * $datetime = new DateTime($v, $datetimezone);
	 * $v = $datetime->getTimestamp();
	 * /*}catch(Exception $y){
	 * if($print){
	 * Debug::print("{$f} something went wrong constructing a DateTime, most likely the string is a unix timestamp");
	 * }
	 * if(preg_match('/^0|^\-?([1-9]+(0-9)*)/', $v)){
	 * Debug::print("{$f} value is probably a unix timestamp");
	 * }else{
	 * Debug::error("{$f} invalid unix timestamp");
	 * }
	 * }*\/
	 * }else{
	 * Debug::error("{$f} column should always be a timestamp");
	 * }
	 * if($print){
	 * Debug::print("{$f} returning \"{$v}\"");
	 * }
	 * return $v;
	 * }catch(Exception $x){
	 * x($f, $x);
	 * }
	 * }
	 */
}

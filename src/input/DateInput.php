<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use function JulianSeymour\PHPWebApplicationFramework\getDateStringFromTimestamp;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\str\DateTimeStringCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use DateTime;
use DateTimeZone;
use Exception;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;

class DateInput extends ChronometricInput implements StaticValueNegotiationInterface{

	public function getTypeAttribute(): string{
		return "date";
	}

	public static function getTypeAttributeStatic(): string{
		return INPUT_TYPE_DATE;
	}

	public function getAllowEmptyInnerHTML(){
		return true;
	}

	public function configure(AjaxForm $form): int{
		$ret = parent::configure($form);
		$datum = $this->getContext();
		if (! $this->hasPredecessors() && $datum->hasHumanReadableName()) {
			$span = new SpanElement($this->getAllocationMode());
			$span->setInnerHTML($datum->getHumanReadableName());
			$this->pushPredecessor($span);
		}
		return $ret;
	}

	public static function negotiateValueStatic(InputInterface $input, Datum $column){
		$f = __METHOD__;
		try {
			$print = false;
			$v = $input->getValueAttribute();
			if ($column instanceof TimestampDatum) {
				if ($print) {
					Debug::print("{$f} column is a TimestampDatum");
				}
				$datetimezone = new DateTimeZone(user()->getTimezone());
				$datetime = new DateTime($v, $datetimezone);
				$v = $datetime->getTimestamp();
			} else {
				$dc = get_short_class($column);
				$cn = $column->getName();
				Debug::error("{$f} column {$cn} should always be a timestamp, but it is a {$dc}");
			}
			if ($print) {
				Debug::print("{$f} returning \"{$v}\"");
			}
			return $v;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getHumanReadableValue($timestamp, $timezone){
		return getDateStringFromTimestamp($timestamp, $timezone);
	}

	public function setValueAttribute($value){
		$f = __METHOD__;
		$print = false;
		if (is_string($value)) {
			if ($print) {
				Debug::print("{$f} value is a string -- assuming it's a valid datetime string");
			}
			return parent::setValueAttribute($value);
		} elseif (is_int($value)) {
			$name = $this->getNameAttribute();
			if ($print) {
				Debug::print("{$f} value of input \"{$name}\" is the integer \"{$value}\" -- about to parse");
			}
			$timezone = user()->getTimezone();
			return parent::setValueAttribute(static::getHumanReadableValue($value, $timezone));
		} elseif ($value instanceof DateTimeStringCommand) {
			if ($print) {
				Debug::print("{$f} value is a DateTimeStringCommand");
			}
			return parent::setValueAttribute($value);
		} elseif ($value instanceof ValueReturningCommandInterface) {
			if ($print) {
				Debug::print("{$f} value is a value-returning command interface, but not a DateTimeStringCommand");
			}
			return parent::setValueAttribute(new DateTimeStringCommand($value));
		}
		Debug::print("{$f} none of the above");
	}
}

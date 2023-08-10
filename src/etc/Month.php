<?php
namespace JulianSeymour\PHPWebApplicationFramework\etc;

use function JulianSeymour\PHPWebApplicationFramework\x;
use Exception;

abstract class Month{

	public static function getMonthName($num){
		$f = __METHOD__;
		try {
			switch ($num) {
				case 1:
					return _("January");
				case 2:
					return _("February");
				case 3:
					return _("March");
				case 4:
					return _("April");
				case 5:
					return _("May");
				case 6:
					return _("June");
				case 7:
					return _("July");
				case 8:
					return _("August");
				case 9:
					return _("September");
				case 10:
					return _("October");
				case 11:
					return _("November");
				case 12:
					return _("December");
				default:
					return _("Invalid month");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}

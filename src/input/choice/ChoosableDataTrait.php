<?php

namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ChoosableDataTrait{

	public abstract static function getPrettyClassName($lang = null);

	public static function getLoadedChoices(?array $selected_keys): ?array{
		$f = __METHOD__;
		$print = false;
		$options = [
			"" => new Choice("", substitute(_("Select %1%"), static::getPrettyClassName()))
		];
		if (! user()->hasForeignDataStructureList(static::getPhylumName())) {
			if ($print) {
				Debug::print("{$f} no carriers");
			}
			return null;
		}
		foreach (user()->getForeignDataStructureList(static::getPhylumName()) as $c) {
			$cid = $c->getIdentifierValue();
			$options[$cid] = new Choice($cid, $c->getName(), ! empty($selected_keys) && in_array($c->getIdentifierValue(), $selected_keys, true));
		}
		return $options;
	}
}

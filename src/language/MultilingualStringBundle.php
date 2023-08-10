<?php
namespace JulianSeymour\PHPWebApplicationFramework\language;

use function JulianSeymour\PHPWebApplicationFramework\config;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\DatumBundle;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\input\TextareaInput;

class MultilingualStringBundle extends DatumBundle
{

	public function generateComponents(?DataStructure $ds = null): array
	{
		$components = [];
		$supported = config()->getSupportedLanguages();
		foreach ($supported as $language) {
			$content = new TextDatum($language, $language);
			$content->setBBCodeFlag(true);
			// $content->setNl2brFlag(true);
			$hrvn = Internationalization::getLanguageNameFromCode($language);
			$content->setHumanReadableName($hrvn);
			$content->setAdminInterfaceFlag(true);
			$content->setNullable(true);
			$content->setElementClass(TextareaInput::class);
			array_push($components, $content);
			if ($this->getNormalizeFlag()) {
				$normalized = new TextDatum("{$language}_normalized");
				$normalized->setBBCodeFlag(true);
				$normalized->setHumanReadableName($hrvn);
				// $normalized->setAdminInterfaceFlag(true);
				$normalized->setNullable(true);
				$normalized->setElementClass(TextareaInput::class);
				$closure = function ($event, $target) use ($normalized) {
					$value = $event->getProperty("value");
					if ($value !== null) {
						$normalized->setValue(NameDatum::normalize($value));
					} else {
						$normalized->ejectValue();
					}
				};
				$content->addEventListener(EVENT_AFTER_SET_VALUE, $closure);
				array_push($components, $normalized);
			}
		}
		return $components;
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"normalize"
		]);
	}

	public function setNormalizeFlag($value = true)
	{
		return $this->setFlag("normalize", $value);
	}

	public function getNormalizeFlag()
	{
		return $this->getFlag("normalize");
	}
}

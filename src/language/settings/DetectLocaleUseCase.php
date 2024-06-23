<?php

namespace JulianSeymour\PHPWebApplicationFramework\language\settings;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\language\Internationalization;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class DetectLocaleUseCase extends UseCase{
	
	public function getActionAttribute(): ?string{
		return "/detect_locale";
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}
	
	public static function detectLocaleStatic():int{
		$f = __METHOD__;
		$print = false;
		$lsd = new LanguageSettingsData();
		$locale = $lsd->getLocaleString();
		deallocate($lsd);
		if(!is_dir("/var/www/locale/{$locale}")){
			$locale = Internationalization::getFallbackLocale($locale);
		}
		$set = setlocale(LC_MESSAGES, $locale, "{$locale}.utf8", "{$locale}.UTF8");
		if(false === $set){
			Debug::error("{$f} setting locale failed");
		}elseif($print){
			Debug::print("{$f} successfully set locale to \"{$set}\"");
		}
		return SUCCESS;
	}
	
	public function execute():int{
		return static::detectLocaleStatic();
	}
}

<?php

namespace JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use JulianSeymour\PHPWebApplicationFramework\admin\AdminOnlyAccountTypePermission;
use ReflectionClass;

class MergeGettextMessageFilesUseCase extends UseCase{
	
	public function execute():int{
		$f = __METHOD__;
		$print = true;
		$locales = [];
		$modules = mods()->getModules();
		foreach($modules as $mod){
			//get the directory the module is located in, look for a directory called locale
			$reflection = new ReflectionClass($mod);
			$filename = $reflection->getFilename();
			$directory = dirname($filename);
			if(!is_dir("{$directory}/locale")){
				if($print){
					Debug::print("{$f} {$directory}/locale is not a directory");
				}
				continue;
			}elseif($print){
				Debug::print("{$f} {$directory}/locale is a directory");
			}
			$subdirectories = glob("{$directory}/locale/*" , GLOB_ONLYDIR);
			if($print){
				Debug::print("{$f} {$directory}/locale contains the following subdirectories:");
				Debug::printArray($subdirectories);
			}
			foreach($subdirectories as $subd){
				if(!is_dir("{$subd}/LC_MESSAGES")){
					if($print){
						Debug::print("{$f} {$subd}/LC_MESSAGES is not a directory");
					}
					continue;
				}elseif($print){
					Debug::print("{$f} {$subd}/LC_MESSAGES is a directory");
				}
				$pofiles = glob("{$subd}/LC_MESSAGES/*.po");
				if(empty($pofiles)){
					if($print){
						Debug::print("{$f} there are no .po files in {$subd}/LC_MESSAGES");
					}
					continue;
				}
				$folder = basename($subd);
				if(!array_key_exists($folder, $locales)){
					$locales[$folder] = [];
				}
				$locales[$folder] = array_merge($locales[$folder], $pofiles);
			}
		}
		if(!empty($locales)){
			if($print){
				Debug::print("{$f} found the following .po files in all module directories:");
				Debug::printArray($locales);
			}
			if(!is_dir("/var/www/locale")){
				mkdir('/var/www/locale', 0777, true);
			}
			foreach($locales as $locale => $filenames){
				if(!is_dir("/var/www/locale/{$locale}/LC_MESSAGES")){
					if(!is_dir("/var/www/locale/{$locale}")){
						mkdir("/var/www/locale/{$locale}", 0777, true);
					}
					mkdir("/var/www/locale/{$locale}/LC_MESSAGES", 0777, true);
				}
				$splat = explode("_", $locale);
				$cmd1 = "msgcat --output-file=/var/www/locale/{$locale}/LC_MESSAGES/messages.po --lang={$splat[0]} --to-code=UTF-8 --use-first";
				foreach($filenames as $fn){
					if(!is_string($fn)){
						Debug::error("{$f} filename is not a string");
					}
					$cmd1 .= " {$fn}";
				}
				if($print){
					Debug::print("{$f} command for merging .po files for locale {$locale} is \"{$cmd1}\"");
				}
				$result = shell_exec($cmd1);
				if($print){
					if($result !== false){
						Debug::print("{$f} result of executing command \"{$cmd1}\" is \"{$result}\"");
					}else{
						Debug::error("{$f} executing command \"{$cmd1}\" failed");
					}
				}
				$cmd2 = "msgfmt /var/www/locale/{$locale}/LC_MESSAGES/messages.po";
				if($print){
					Debug::print("{$f} command for generating .mo file for locale {$locale} is \"{$cmd2}\"");
				}
				$result = shell_exec($cmd2);
				if($print){
					if($result !== false){
						Debug::print("{$f} result of executing command \"{$cmd2}\" is \"{$result}\"");
					}else{
						Debug::error("{$f} executing command \"{$cmd2}\" failed");
					}
				}
			}
			if($print){
				Debug::print("{$f} finished merging .po files for ".count($locales)." locales");
			}
		}elseif($print){
			Debug::print("{$f} there are no .po files in any module directories");
		}
		return SUCCESS;
	}
	
	public function getActionAttribute(): ?string{
		return "/gettext";
	}

	protected function getExecutePermissionClass(){
		return AdminOnlyAccountTypePermission::class;
	}
}
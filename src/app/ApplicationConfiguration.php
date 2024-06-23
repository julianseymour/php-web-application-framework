<?php

namespace JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\getCurrentUserKey;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use mysqli;

abstract class ApplicationConfiguration extends Basic{

	public function getDefaultWorkflowClass(): string{
		$f = __METHOD__;
		switch(APPLICATION_INTEGRATION_MODE){
			case APP_INTEGRATION_MODE_STANDALONE:
				return \JulianSeymour\PHPWebApplicationFramework\app\workflow\StandardWorkflow::class;
			case APP_INTEGRATION_MODE_UNIVERSAL:
				return \JulianSeymour\PHPWebApplicationFramework\app\workflow\UniversalWorkflow::class;
			default:
				Debug::error("{$f} invalid application integration mode \"" . APPLICATION_INTEGRATION_MODE . "\"");
		}
	}

	public function getModuleClasses(): ?array{
		return [
			DefaultModule::class
		];
	}

	public function getFooterElementClass():?string{
		return null; //FooterElement::class;
	}
	
	public function isLanguageSupported(?string $lang): bool{
		$f = __METHOD__;
		$print = false;
		if($lang instanceof \JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface){
			$lang = $lang;
			while($lang instanceof \JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface){
				$lang = $lang->evaluate();
			}
		}elseif($print){
			Debug::print("{$f} language is not a value-returning command");
		}
		if(!is_string($lang)){
			$gottype = is_object($lang) ? $lang->getClass() : gettype($lang);
			Debug::error("{$f} language code must be a string, but you passed a {$gottype}");
		}elseif(empty($lang)){
			Debug::error("{$f} language code cannot be an empty string");
		}
		$lang = strtolower($lang);
		return false !== array_search($lang, $this->getSupportedLanguages());
	}

	/**
	 * override this to return a different user class
	 *
	 * @return string
	 */
	public function getNormalUserClass():string{
		return \JulianSeymour\PHPWebApplicationFramework\account\NormalUser::class;
	}

	public function getAdministratorClass():string{
		return \JulianSeymour\PHPWebApplicationFramework\admin\Administrator::class;
	}
	
	public function getGuestUserClass():string{
		return \JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser::class;
	}
	
	public function getShadowUserClass():string{
		return \JulianSeymour\PHPWebApplicationFramework\account\shadow\ShadowUser::class;
	}
	
	public function getHTMLElementClass():string{
		return \JulianSeymour\PHPWebApplicationFramework\ui\RiggedHTMLElement::class;
	}
	
	/**
	 * returns a list of languages supported by this application
	 *
	 * @return string[]
	 */
	public function getSupportedLanguages(): ?array{
		return [
			LANGUAGE_DEFAULT
		];
	}

	/**
	 * override this to configure the default loadout that is generated for all use cases
	 *
	 * @param DataStructure $object
	 * @return string
	 */
	public function getLoadoutGeneratorClass(?DataStructure $object = null): ?string{
		return null;
	}

	public function getTrimmableColumnNames(DataStructure $obj): ?array{
		if($obj instanceof \JulianSeymour\PHPWebApplicationFramework\account\PlayableUser && $obj->getIdentifierValue() === getCurrentUserKey()){
			return null;
		}elseif($obj->getOperandFlag()){
			return null;
		}
		return $obj->getFilteredColumnNames(COLUMN_FILTER_TRIMMABLE, "!" . COLUMN_FILTER_ENCRYPTED, "!" . COLUMN_FILTER_DEFAULT, "!" . COLUMN_FILTER_FOREIGN, "!" . COLUMN_FILTER_VIRTUAL, "!" . COLUMN_FILTER_VALUED);
	}

	public function beforeInstallHook(): int{
		$f = __METHOD__;
		foreach(mods()->getModules() as $mod){
			$status = $mod->beforeInstallHook();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} " . get_class($mod) . "->beforeInstallHook returned error status \"{$err}\"");
				return $status;
			}
		}
		return SUCCESS;
	}

	public function afterInstallHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$print = false;
		$mods = mods()->getModules();
		if(empty($mods)){
			if($print){
				Debug::print("{$f} no mods");
			}
			return SUCCESS;
		}elseif($print){
			Debug::print("{$f} " . count($mods) . " mods");
		}
		foreach($mods as $mod){
			$status = $mod->afterInstallHook($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} " . get_class($mod) . "->afterInstallHook returned error status \"{$err}\"");
				return $status;
			}elseif($print){
				Debug::print("{$f} " . get_class($mod) . "->afterInstallHook was successful");
			}
		}
		return SUCCESS;
	}

	public function afterAccountCreationHook(mysqli $mysqli, \JulianSeymour\PHPWebApplicationFramework\account\PlayableUser $user): int{
		$f = __METHOD__;
		$print = false;
		$mods = mods()->getModules();
		if($print){
			$count = count($mods);
			Debug::print("{$f} {$count} modules");
		}
		foreach($mods as $mod){
			$status = $mod->afterAccountCreationHook($mysqli, $user);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} account creation hook for one of your modules returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
		}
		if($print){
			Debug::print("{$f} all modules executed afterAccountCreationHook successfully");
		}
		return SUCCESS;
	}
	
	public function getModuleBundlerClass():string{
		return ModuleBundler::class;
	}
	
	public function getDefaultPlaceholderMode(){
		return INPUT_PLACEHOLDER_MODE_NORMAL;
	}
}

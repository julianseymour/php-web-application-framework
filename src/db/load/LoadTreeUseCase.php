<?php
namespace JulianSeymour\PHPWebApplicationFramework\db\load;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\lazy;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\language\settings\LanguageSettingsSessionData;
use JulianSeymour\PHPWebApplicationFramework\ui\RiggedLoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\use_case\SubsequentUseCase;
use Exception;
use mysqli;

class LoadTreeUseCase extends SubsequentUseCase
{

	/**
	 * load all data necessary for the use case to function correctly
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function execute(): int{
		$f = __METHOD__;
		try {
			$print = false;
			$mysqli = db()->getConnection(PublicReadCredentials::class);
			if ($mysqli == null) {
				Debug::error("{$f} mysqli connection error");
				return $this->setObjectStatus(ERROR_MYSQL_CONNECT);
			} elseif ($mysqli->connect_errno) {
				Debug::error("{$f} Failed to connect to MySQL: ({$mysqli->connect_errno}) {$mysqli->connect_error}");
			} elseif (! $mysqli->ping()) {
				Debug::error("{$f} mysqli connection failed ping test: \"" . $mysqli->error . "\"");
			}
			if ($print) {
				$mem = memory_get_usage();
				Debug::print("{$f} memory loading: {$mem}");
			}
			$predecessor = $this->getPredecessor();
			$status = $predecessor->beforeLoadHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeLoadHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$user = user();
			if ($user == null) {
				Debug::error("{$f} user data is undefined");
				return $this->setObjectStatus(ERROR_NULL_USER_OBJECT);
			}
			// set language //XXX TODO move this elsewhere
			$language_settings = new LanguageSettingsSessionData();
			$language = $user->getLanguagePreference();
			if (! isset($language)) {
				Debug::error("{$f} language is undefined");
			}
			$language_settings->setLanguageCode($language);
			// automatically load hierarchical data from database
			$generator = $predecessor->getLoadoutGenerator($user);
			if ($generator instanceof LoadoutGenerator) {
				if($print){
					$lgc = $generator->getClass();
					Debug::print("{$f} loadout generator class is \"{$lgc}\"");
				}
				$loadout = $generator->generateRootLoadout($user, $predecessor);
			} else{
				if ($print) {
					Debug::print("{$f} generator class is null");
				}
				$loadout = new Loadout();
			}
			if(!Request::isAjaxRequest()){
				if($print){
					Debug::print("{$f} this is not an AJAX request, adding dependencies from RiggedLoadoutGenerator");
				}
				if(!$loadout){
					$loadout = new Loadout();
				}
				$rigged = new RiggedLoadoutGenerator();
				$loadout->addDependencies($rigged->getRootNodeTreeSelectStatements($user, $predecessor));
				if ($print) {
					$pc = get_short_class($predecessor);
					Debug::print("{$f} predecessor of class \"{$pc}\" generated the following loadout:");
					$loadout->debugPrint();
				}
			}elseif($print){
				Debug::print("{$f} this is an AJAX request");
			}
			if($loadout instanceof Loadout && $loadout->hasTreeSelectStatements()){
				if($print){
					Debug::print("{$f} loadout is a Loadout and has tree select statements");
				}
				$status = $loadout->expandTree($mysqli, $user);
				if (! isset($status)) {
					Debug::error("{$f} status is undefined");
				} elseif ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} tree expansion returned error status \"{$err}\"");
				} elseif ($print) {
					Debug::print("{$f} tree expansion successful");
				}
				lazy()->processQueues($mysqli);
				// $user->trimUnusedColumns(true, 3);
				$loadout->dispose();
			}elseif($print){
				Debug::print("{$f} no loadout, or loadout has no tree select statements");
			}
			app()->advanceExecutionState(EXECUTION_STATE_LOADED);
			$status = $predecessor->afterLoadHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} afterLoadHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			return $status;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getUseCaseId()
	{
		return USE_CASE_LOAD_TREE;
	}

	public function getActionAttribute(): ?string
	{
		return $this->getPredecessor()->getActionAttribute();
	}
}

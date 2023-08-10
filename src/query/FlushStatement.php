<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\table\MultipleTableNamesTrait;

class FlushStatement extends QueryStatement
{

	use LocalFlagBearingTrait;
	use MultipleTableNamesTrait;

	public function __construct()
	{
		parent::__construct();
		$this->requirePropertyType("channelRelayLogs", 's');
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"binary logs",
			"engine logs",
			"error logs",
			"export",
			"general logs",
			"hosts",
			"logs",
			"optimizer costs",
			"privileges",
			"read lock",
			"relay logs",
			"slow logs",
			"status",
			"tables",
			"user_resources"
		]);
	}

	public function setBinaryLogsFlag($value = true)
	{
		return $this->setFlag('binary logs', $value);
	}

	public function getBinaryLogsFlag()
	{
		return $this->getFlag('binary logs');
	}

	public function binaryLogs()
	{
		$this->setBinaryLogsFlag(true);
		return $this;
	}

	public function setEngineLogsFlag($value = true)
	{
		return $this->setFlag('engine logs', $value);
	}

	public function getEngineLogsFlag()
	{
		return $this->getFlag('engine logs');
	}

	public function engineLogs()
	{
		return $this->withFlag("engine logs", true);
	}

	public function setErrorLogsFlag($value = true)
	{
		return $this->setFlag('error logs', $value);
	}

	public function getErrorLogsFlag()
	{
		return $this->getFlag('error logs');
	}

	public function errorLogs()
	{
		return $this->withFlag("error logs", true);
	}

	public function setGeneralLogsFlag($value = true)
	{
		return $this->setFlag('general logs', $value);
	}

	public function getGeneralLogsFlag()
	{
		return $this->getFlag('general logs');
	}

	public function generalLogs()
	{
		return $this->withFlag("general logs", true);
	}

	public function setHostsFlag($value = true)
	{
		return $this->setFlag('hosts', $value);
	}

	public function getHostsFlag()
	{
		return $this->getFlag('hosts');
	}

	public function hosts()
	{
		return $this->withFlag("hosts", true);
	}

	public function setLogsFlag($value = true)
	{
		return $this->setFlag('logs', $value);
	}

	public function getLogsFlag()
	{
		return $this->getFlag('logs');
	}

	public function logs()
	{
		return $this->withFlag('logs', true);
	}

	public function setPrivilegesFlag($value = true)
	{
		return $this->setFlag('privileges', $value);
	}

	public function getPrivilegesFlag()
	{
		return $this->getFlag('privileges');
	}

	public function privileges()
	{
		return $this->withFlag("privileges", true);
	}

	public function setOptimizerCostsFlag($value = true)
	{
		return $this->setFlag("optimizer costs", $value);
	}

	public function getOptimizerCostsFlag()
	{
		return $this->getFlag("optimizer costs");
	}

	public function optimizerCosts()
	{
		return $this->withFlag("optimizer costs", true);
	}

	public function setRelayLogsFlag($value = true)
	{
		return $this->setFlag("relay logs", $value);
	}

	public function getRelayLogsFlag()
	{
		return $this->getFlag("relay logs");
	}

	public function relayLogs(...$values)
	{
		if (isset($values)) {
			$this->setChannelRelayLogs($values);
		}
		return $this->withFlag("relay logs", true);
	}

	public function setSlowLogsFlag($value = true)
	{
		return $this->setFlag("slow logs", $value);
	}

	public function getSlowLogsFlag()
	{
		return $this->getFlag("slow logs");
	}

	public function slowLogs()
	{
		return $this->withFlag("slow logs", true);
	}

	public function setStatusFlag($value = true)
	{
		return $this->setFlag("status", $value);
	}

	public function getStatusFlag()
	{
		return $this->getFlag("status");
	}

	public function setUserResourcesFlag($value = true)
	{
		return $this->setFlag("user_resources", $value);
	}

	public function getUserResourcesFlag()
	{
		return $this->getFlag("user_resources");
	}

	public function userResources()
	{
		return $this->withFlag("user_resources", true);
	}

	public function setTablesFlag($value = true)
	{
		return $this->setFlag("tables", $value);
	}

	public function getTablesFlag()
	{
		return $this->getFlag("tables");
	}

	public function setReadLockFlag($value = true)
	{
		return $this->setFlag("read lock", $value);
	}

	public function getReadLockFlag()
	{
		return $this->getFlag("read lock");
	}

	public function withReadLock()
	{
		return $this->withFlag("read lock", true);
	}

	public function setExportFlag($value = true)
	{
		$f = __METHOD__; //FlushStatement::getShortClass()."(".static::getShortClass().")->setEcportFlag()";
		if ($value && ! $this->hasTableNames()) {
			Debug::error("{$f} do not call this function unless table names have already been defined");
		}
		return $this->setFlag("export", $value);
	}

	public function getExportFlag()
	{
		return $this->getFlag("export");
	}

	public function forExport()
	{
		return $this->withFlag("export", true);
	}

	public function setChannelRelayLogs($values)
	{
		return $this->setArrayProperty("channelRelayLogs", $values);
	}

	public function hasChannelRelayLogs()
	{
		return $this->hasArrayProperty("channelRelayLogs");
	}

	public function pushChannelRelayLogs(...$values)
	{
		return $this->pushArrayProperty("channelRelayLogs", ...$values);
	}

	public function mergeChannelRelayLogs($values)
	{
		return $this->mergeArrayProperty("channelRelayLogs", $values);
	}

	public function getChannelRelayLogs()
	{
		return $this->getProperty("channelRelayLogs");
	}

	public function getChannelRelayLogsCount()
	{
		return $this->getArrayPropertyCount("channelRelayLogs");
	}

	public function getQueryStatementString(): string
	{
		// FLUSH
		$string = "flush ";
		// [NO_WRITE_TO_BINLOG | LOCAL]
		if ($this->getLocalFlag()) {
			$string .= "local ";
		}
		if ($this->getTablesFlag()) {
			// tables_option: {
			// TABLES
			// | TABLES tbl_name [, tbl_name] ...
			// | TABLES WITH READ LOCK
			// | TABLES tbl_name [, tbl_name] ... WITH READ LOCK
			// | TABLES tbl_name [, tbl_name] ... FOR EXPORT
			// }
			$string .= "tables";
			if ($this->hasTableNames()) {
				$string .= " " . implode(',', $this->getTableNames());
			}
			if ($this->getReadLockFlag()) {
				$string .= " with read lock";
			} elseif ($this->getExportFlag()) {
				$string .= " for export";
			}
		} else {
			// BINARY LOGS
			if ($this->getBinaryLogsFlag()) {
				$string .= " binary logs";
			}
			// | ENGINE LOGS
			if ($this->getEngineLogsFlag()) {
				$string .= " engine logs";
			}
			// | ERROR LOGS
			if ($this->getErrorLogsFlag()) {
				$string .= " error logs";
			}
			// | GENERAL LOGS
			if ($this->getGeneralLogsFlag()) {
				$string .= " general logs";
			}
			// | HOSTS
			if ($this->getHostsFlag()) {
				$string .= " hosts";
			}
			// | LOGS
			if ($this->getLogsFlag()) {
				$string .= " logs";
			}
			// | PRIVILEGES
			if ($this->getPrivilegesFlag()) {
				$string .= " privileges";
			}
			// | OPTIMIZER_COSTS
			if ($this->getOptimizerCostsFlag()) {
				$string .= " optimizer costs";
			}
			// | RELAY LOGS [FOR CHANNEL channel]
			if ($this->hasChannelRelayLogs()) {
				foreach ($this->getChannelRelayLogs() as $channel) {
					$string .= " relay logs for {$channel}";
				}
			} elseif ($this->getRelayLogsFlag()) {
				$string .= " relay logs";
			}
			// | SLOW LOGS
			if ($this->getSlowLogsFlag()) {
				$string .= " slow logs";
			}
			// | STATUS
			if ($this->getStatusFlag()) {
				$string .= " status";
			}
			// | USER_RESOURCES
			if ($this->getUserResourcesFlag()) {
				$string .= " user_resources";
			}
		}
		return $string;
	}
}

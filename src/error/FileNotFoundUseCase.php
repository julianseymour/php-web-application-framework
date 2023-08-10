<?php
namespace JulianSeymour\PHPWebApplicationFramework\error;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\security\nonexistent_uris\NonexistentUriData;
use JulianSeymour\PHPWebApplicationFramework\security\condemn\CondemnIpAddressUseCase;
use JulianSeymour\PHPWebApplicationFramework\use_case\StatusCodeUseCase;
use Exception;

class FileNotFoundUseCase extends StatusCodeUseCase{

	public function getObjectStatus(): int{
		return 404;
	}

	public function getPageContent(): ?array{
		return [
			new FileNotFoundElement(ALLOCATION_MODE_EAGER, user())
		];
	}

	public function blockCrossOriginRequest(){
		exit();
	}

	public function execute():int{
		$f = __METHOD__;
		try{
			$print = false;
			$segments = request()->getRequestURISegments();
			if($print){
				Debug::print("{$f} requested URI {$_SERVER['REQUEST_URI']}");
				Debug::printArray($segments);
			}
			$select = NonexistentUriData::selectStatic()->where(
				new WhereCondition("uriSegment", OPERATOR_EQUALS)
			)->withTypeSpecifier('s')->withParameters($segments[0]);
			$mysqli = db()->reconnect(PublicWriteCredentials::class);
			
			if(!NonexistentUriData::tableExistsStatic($mysqli)){
				NonexistentUriData::createTableStatic($mysqli);
			}
			
			$result = $select->executeGetResult($mysqli);
			if($result->num_rows === 0){
				if($print){
					Debug::print("{$f} URI {$segments[0]} was not already inserted, doing so now");
				}
				$neu = new NonexistentUriData();
				$neu->setIpAddress($_SERVER['REMOTE_ADDR']);
				$neu->setColumnValue("uriSegment", $segments[0]);
				$neu->setColumnValue("requestUri", $_SERVER['REQUEST_URI']);
				$status = $neu->insert($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} inserting nonexistent URI data returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("{$f} successfully inserted nonexistent URI data");
				}
			}else{
				if($print){
					Debug::print("{$f} URI {$segments[0]} was already inserted");
				}
				$results = $result->fetch_all(MYSQLI_ASSOC);
				$list = $results[0]['list'];
				if($list === POLICY_BLOCK){
					if($print){
						Debug::warning("{$f} IP address {$_SERVER['REMOTE_ADDR']} is attempting to access a blocked nonexistent URI \"{$segments[0]}\"");
					}
					$condemn = new CondemnIpAddressUseCase($this);
					$condemn->validateTransition();
					$condemn->setUri($_SERVER['REQUEST_URI']);
					$condemn->setReasonLogged(BECAUSE_VULNERABILITY_SCANNER);
					return $condemn->execute();
				}elseif($print){
					Debug::print("{$f} nonexistent URI \"{$segments[0]}\" was  not blocked yet");
				}
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}

<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;

use function JulianSeymour\PHPWebApplicationFramework\db;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\CounterpartKeyColumnInterface;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\notification\NoteworthyInterface;
use JulianSeymour\PHPWebApplicationFramework\use_case\SubsequentUseCase;
use Exception;
use mysqli;

class InsertUseCase extends SubsequentUseCase{

	public function execute():int{
		$f = __METHOD__;
		try{
			$print = false;
			$inserted_object = $this->getPredecessor()->getDataOperandObject();
			if($print){
				$ioc = $inserted_object->getClass();
				Debug::print("{$f} inserted object class is \"{$ioc}\"");
			}
			// 1. connect to database
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if(!isset($mysqli)){
				$err = ErrorMessage::getResultMessage(ERROR_MYSQL_CONNECT);
				Debug::error("{$f} {$err}");
				return $this->setObjectStatus(ERROR_MYSQL_CONNECT);
			}
			// 2. initialize data operand for insertion
			if($inserted_object instanceof CounterpartKeyColumnInterface){
				$inserted_object->setRoleAsCounterpart(COUNTERPART_ROLE_INSTIGATOR);
			}
			$inserted_object->setReceptivity(DATA_MODE_RECEPTIVE);
			// 3. throttle
			if(user() instanceof Administrator){
				if($print){
					Debug::print("{$f} user is administrator, skipping throttlage");
				}
			}elseif(
				$inserted_object->hasColumn("insertIpAddress") && 
				$inserted_object->getColumn("insertIpAddress")->getPersistenceMode() === PERSISTENCE_MODE_DATABASE && 
				$inserted_object->throttleOnInsert()
			){
				if(!$inserted_object->throttle($mysqli)){
					Debug::warning("{$f} whoa slow down there fella and let your brain catch up to your fingers");
					return $this->setObjectStatus(ERROR_MESSAGE_FLOOD);
				}
			}
			// 4. process form
			$form = $this->getPredecessor()->getProcessedFormObject();
			$indices = array_keys($form->getFormDataIndices($inserted_object));
			if(empty($indices)){
				Debug::error("{$f} processed form indices array is empty");
				return FAILURE;
			}
			$operand_class = $inserted_object->getClass();
			if($print){
				$form_class = $form->getClass();
				Debug::print("{$f} about to call {$operand_class}->processForm({$form_class}, POST)");
			}
			$files = request()->hasRepackedIncomingFiles() ? request()->getRepackedIncomingFiles() : null;
			$post = getInputParameters();
			$status = $inserted_object->processForm($form, $post, $files);
			$form = null;
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} processing array for insert returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$inserted_object->setAutoloadFlags(true);
			$status = $inserted_object->loadForeignDataStructures($mysqli, false, 3);
			if($status !== SUCCESS){
				Debug::warning("{$f} loading foreign data structures of inserted object returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// 5. insert
			$status = $inserted_object->insert($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} inserting generic product returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// 6. reload object
			$status = $inserted_object->reload($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} reloaded object has error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} reloaded object successfully");
			}
			// 7. send notifications if necessary
			if(
				$inserted_object instanceof NoteworthyInterface && 
				// && $inserted_object->isNotificationDataWarranted(user())
				!$inserted_object->getDisableNotificationsFlag()
			){
				if($print){
					Debug::print("{$f} notification is warranted");
				}
				$status = $this->insertNotificationData($mysqli, $inserted_object);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} updating notifications returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("{$f} successfully updated notification data");
				}
			}elseif($print){
				Debug::print("{$f} notification is not necessary");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	/**
	 * this only gets called by processInsertOperation
	 *
	 * @param mysqli $mysqli
	 * @param DataStructure $subject
	 * @return int
	 */
	protected function insertNotificationData(mysqli $mysqli, $subject){
		$f = __METHOD__;
		try{
			$print = false;
			$status = $subject->getObjectStatus();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} reloaded object has error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			if(!user()->hasCorrespondentObject()){
				Debug::warning("{$f} user's correspondent object is undefined");
				$this->acquireCorrespondentObject($mysqli);
			}
			user()->setTemporaryRole(USER_ROLE_SENDER);
			if($subject instanceof UserOwned && !$subject->hasUserTemporaryRole()){
				$subject->setUserTemporaryRole(USER_ROLE_SENDER);
			}
			if(user()->hasCorrespondentObject()){
				$correspondent = user()->getCorrespondentObject();
				if(user()->getIdentifierValue() === $correspondent->getIdentifierValue()){
					if($print){
						Debug::print("{$f} user and correspondent are the same person");
					}
					return SUCCESS;
				}elseif($correspondent instanceof PlayableUser){
					$correspondent->setTemporaryRole(USER_ROLE_RECIPIENT);
					if(!$correspondent->hasCorrespondentObject()){
						$correspondent->setCorrespondentObject(user());
					}
					$counterpart = $subject->getCounterpartObject();
					$status = $counterpart->reload($mysqli);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} reloading counterpart returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}
					$correspondentKey = $correspondent->getIdentifierValue();
					if($print){
						Debug::print("{$f} about to send notification to correspondent with key \"{$correspondentKey}\"");
					}
					$status = $correspondent->notifyIfWarranted($mysqli, $counterpart);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} sending correspondent's notification returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}elseif($print){
						Debug::print("{$f} successfully sent notification to correspondent");
					}
				}elseif($print){
					Debug::print("{$f} correspondent is not a real user");
				}
			}elseif($print){
				Debug::print("{$f} user does not have a correspondent");
			}
			$status = user()->notifyIfWarranted($mysqli, $subject);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} sending user's notification returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} successfully sent notification to user");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}

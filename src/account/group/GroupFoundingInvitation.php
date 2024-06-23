<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\group;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

/**
 * the invitation a group founder sends to themself when creating a new group
 *
 * @author j
 *        
 */
class GroupFoundingInvitation extends GroupInvitation
{

	protected function afterGenerateKeyHook($key): int{
		$f = __METHOD__;
		$print = false;
		if($this->hasGroupData()){
			if($print){
				Debug::print("{$f} group object is defined");
			}
			$group = $this->getGroupData();
			if($group->getInsertFlag()){
				if($print){
					Debug::print("{$f} insert flag is set -- this is the group's founding invitation");
				}

				if($group->hasPrivateKey()){
					Debug::error("{$f} group already has a private key");
				}
				$keypair = sodium_crypto_box_keypair();
				$group->setPublicKey(sodium_crypto_box_publickey($keypair));
				$this->setGroupPrivateKey(sodium_crypto_box_secretkey($keypair));
			}elseif($print){
				Debug::print("{$f} group object is not flagged for insert -- this is not the group's founding invitation");
			}
		}elseif($print){
			Debug::print("{$f} group object is undefined");
		}
		return parent::afterGenerateKeyHook($key);
	}
}

<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\avatar;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\image\ImageData;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateContextInterface;
use Exception;
use mysqli;

class ProfileImageData extends ImageData implements TemplateContextInterface{

	public function getWebFileDirectory():string{
		return "/images/profile/" . $this->getUserKey();
	}

	public function getArrayMembershipConfiguration($config_id): ?array{
		$f = __METHOD__;
		try{
			$config = parent::getArrayMembershipConfiguration($config_id);
			switch($config_id){
				case "default":
				default:
					$config["thumbnailHeight"] = true;
					$config["thumbnailWidth"] = true;
					$config['webThumbnailPath'] = true;
					return $config;
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function getSubtypeStatic():string{
		return IMAGE_TYPE_PROFILE;
	}

	public function template(){
		return;
	}

	public function beforeInsertHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$print = false;
		$ret = parent::beforeInsertHook($mysqli);
		if(!$this->hasUserKey()){
			Debug::error("{$f} user key is undefined");
		}elseif($print){
			Debug::print("{$f} user key is \"" . $this->getUserKey() . "\"");
		}
		return $ret;
	}
	
	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null):void{
		parent::reconfigureColumns($columns, $ds);
		$columns['userKey']->setConverseRelationshipKeyName("profileImageKey");
	}
}

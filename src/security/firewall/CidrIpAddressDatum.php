<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\IpAddressDatum;

class CidrIpAddressDatum extends IpAddressDatum{

	public function processInput($input){
		$f = __METHOD__;
		$print = false;
		$value = $input->getValueAttribute(); // arr[$name];
		Debug::print("{$f} posted value is \"{$value}\"");
		$data = $this->getDataStructure();
		if(preg_match(REGEX_IPv4_ADDRESS, $value)){
			if($print){
				Debug::print("{$f} posted value is an IPv4 address");
			}
			$version = 4;
			$mask = 32;
			$ip_address = $value;
		}elseif(preg_match(REGEX_IPv4_CIDR, $value)){
			if($print){
				Debug::print("{$f} posted value is an IPv4 CIDR range");
			}
			$splat = explode("/", $value);
			$ip_address = $splat[0];
			$mask = intval($splat[1]);
			$version = 4;
		}elseif(preg_match(REGEX_IPv6_ADDRESS, $value)){
			if($print){
				Debug::print("{$f} posted value is an IPv6 address");
			}
			$version = 6;
			$ip_address = $value;
			$mask = 128;
			//return $this->setObjectStatus(ERROR_IPv6_UNSUPPORTED);
		}elseif(preg_match(REGEX_IPv6_CIDR, $value)){
			if($print){
				Debug::print("{$f} posted value is an IPv6 CIDR range");
			}
			$version = 6;
			$splat = explode("/", $value);
			$ip_address = $splat[0];
			$mask = intval($splat[1]);
			//return $this->setObjectStatus(ERROR_IPv6_UNSUPPORTED);
		}else{
			if($print){
				Debug::print("{$f} invalid IP address \"{$value}\"");
			}
			return $this->setObjectStatus(ERROR_INVALID_IP_ADDRESS);
		}
		if(
			$data->getIpVersion() !== $version 
			|| $this->getValue() !== $ip_address 
			|| $data->getMask() !== $mask
		){
			if($print){
				Debug::print("{$f} IP address, version or mask has changed");
			}
			$data->setIpVersion($version);
			$this->setValue($ip_address);
			$data->setMask($mask);
			return SUCCESS;
		}elseif($print){
			Debug::print("{$f} IP address, version and mask have not changed");
		}
		return STATUS_UNCHANGED;
	}
}

<?php
	namespace JulianSeymour\PHPWebApplicationFramework\security;
	
	use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use Exception;
	
abstract class AccessControlPanelElement extends DivElement
{
	
	public abstract static function allowNewEntry():bool;
	
	public abstract static function getDataStructureClass():string;
	
	public abstract static function getFormClass():string;
	
	public function __construct(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$this->addClassAttribute("account_firewall");
	}
	
	public function generateChildNodes(): ?array
	{
		$f = __METHOD__; //AccessControlPanelElement::getShortClass()."(".static::getShortClass().")->generateChildNodes()";
		try {
			$print = false;
			$context = $this->getContext();
			$ds_class = $this->getDataStructureClass();
			$family = $ds_class::getPhylumName();
			$arr = $context->hasForeignDataStructureList($family) ? $context->getForeignDataStructureList($family) : [];
			if($print){
				Debug::print("{$f} ".count($arr)." ".$ds_class::getPrettyClassNames());
			}
			$mode = $this->getAllocationMode();
			$ip_whitelist = new DivElement();
			$ip_whitelist->addClassAttribute("ip_list");
			$div1 = new DivElement();
			$div1->setIdAttribute("whitelist_top");
			$div1->setInnerHTML(_("Allow IP addresses"));
			$ip_whitelist->appendChild($div1);
			$form_class = $this->getFormClass();
			$counter = 0;
			foreach ($arr as $ip) {
				if ($ip->getList() !== POLICY_ALLOW) {
					if($print){
						Debug::print("{$f} this is not a whitelisted IP address");
					}
					continue;
				}
				$cidr_ip_form = new $form_class($mode, $ip);
				$ip_whitelist->appendChild($cidr_ip_form);
				$counter ++;
			}
			if(static::allowNewEntry()){
				$new_ip = new $ds_class();
				$new_ip->setUserData(user());
				$new_ip->setList(POLICY_ALLOW);
				$new_whitelist_form = new $form_class(ALLOCATION_MODE_LAZY, $new_ip);
				$new_whitelist_form->setIdAttribute("new_authorized_ip_form");
				$ip_whitelist->appendChild($new_whitelist_form);
			}
			$this->appendChild($ip_whitelist);
			$ip_blacklist = new DivElement();
			$ip_blacklist->addClassAttribute("ip_list");
			$div2 = new DivElement();
			$div2->setIdAttribute("blacklist_top");
			$div2->setInnerHTML(_("Blocked IP addresses"));
			$ip_blacklist->appendChild($div2);
			$counter = 0;
			foreach ($arr as $ip) {
				if ($ip->getList() !== POLICY_BLOCK) {
					if($print){
						Debug::print("{$f} this is not a blacklisted IP address");
					}
					continue;
				}
				$cidr_ip_form = new $form_class($mode, $ip);
				$ip_blacklist->appendChild($cidr_ip_form);
				$counter ++;
			}
			if(static::allowNewEntry()){
				$new_ip = new $ds_class();
				$new_ip->setUserData(user());
				$new_ip->setList(POLICY_BLOCK);
				$new_blacklist_form = new $form_class(ALLOCATION_MODE_LAZY, $new_ip);
				$new_blacklist_form->setIdAttribute("new_blocked_ip_form");
				$ip_blacklist->appendChild($new_blacklist_form);
			}
			$this->appendChild($ip_blacklist);
			$ip_graylist = new DivElement();
			$ip_graylist->addClassAttribute("ip_list");
			$div3 = new DivElement();
			$div3->setIdAttribute("graylist_top");
			$div3->setInnerHTML(_("Unauthorized IP addresses"));
			$ip_graylist->appendChild($div3);
			$counter = 0;
			foreach ($arr as $ip) {
				if ($ip->isUninitialized()) {
					if($print){
						$decl = $ip->getDeclarationLine();
						Debug::print("{$f} skipping uninitialized ".$ds_class::getPrettyClassName()." declared {$decl}");
					}
					continue;
				} elseif ($ip->getList() !== POLICY_DEFAULT) {
					if($print){
						Debug::print("{$f} this is not an unauthorized IP address");
					}
					continue;
				}
				$cidr_ip_form = new $form_class($mode, $ip);
				$ip_graylist->appendChild($cidr_ip_form);
				$counter ++;
			}
			$nothing_here = new DivElement();
			$nothing_here->addClassAttribute("nothing_here");
			$nothing_here->setInnerHTML(_("Nothing here. This list will display IP addresses that attempt to access your account."));
			$ip_graylist->appendChild($nothing_here);
			$this->appendChild($ip_graylist);
			return $this->getChildNodes();
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}

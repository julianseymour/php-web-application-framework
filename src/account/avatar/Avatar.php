<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\avatar;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\online\OnlineStatusIndicator;
use JulianSeymour\PHPWebApplicationFramework\command\control\NodeBearingIfCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetForeignDataStructureCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\AppendChildCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\style\StyleSheetPathTrait;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateElementInterface;
use Exception;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;

class Avatar extends SpanElement implements TemplateElementInterface{

	use StyleSheetPathTrait;
	
	protected $avatarType;

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		$this->setAvatarType(AVATAR_TYPE_UNDEFINED);
		parent::__construct($mode, $context);
		$this->addClassAttribute("avatar_c");
	}

	public static function isTemplateworthy(): bool{
		return true;
	}

	public function setAvatarType($type){
		if($this->hasAvatarType()){
			$this->release($this->avatarType);
		}
		return $this->avatarType = $this->claim($type);
	}

	public function getAvatarType(){
		return $this->avatarType;
	}

	public function hasAvatarType():bool{
		return isset($this->avatarType);
	}
	
	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try{
			$mode = $this->getAllocationMode();
			$context = $this->getContext();
			$this->setIdOverride("avatar");
			$avatar_bg = new DivElement($mode);
			$avatar_bg->addClassAttribute("avatar_bg");
			$avatar_bg->setIdOverride("avatar_bg");
			if($this->getAvatarType() === AVATAR_TYPE_PREVIEW){
				$avatar_bg->setIdAttribute("msg_preview_avatar");
			}
			$avatar_head = new DivElement($mode);
			$avatar_head->addClassAttribute("avatar_head");
			$avatar_head->setAllowEmptyInnerHTML(true);
			$avatar_bg->appendChild($avatar_head);
			$avatar_torso = new DivElement($mode);
			$avatar_torso->addClassAttribute("avatar_torso");
			$avatar_torso->setAllowEmptyInnerHTML(true);
			$avatar_bg->appendChild($avatar_torso);
			$pid = new GetForeignDataStructureCommand($context, "profileImageKey");
			$thumb = new ProfileImageThumbnail($mode);
			$thumb->setIdOverride("thumb");
			$bind = $thumb->bindElementCommand($pid);
			$if = NodeBearingIfCommand::if(
				new AndCommand(
					$context->hasForeignDataStructureCommand("profileImageKey"),
					$pid->hasColumnValueCommand("status")
				)
			)->then(
				new AppendChildCommand($avatar_bg, $bind)
			);
			$avatar_bg->resolveTemplateCommand($if);
			if(!$this->getTemplateFlag()){
				$avatar_bg->disableDeallocation();
				deallocate($if);
				$avatar_bg->enableDeallocation();
			}
			$this->appendChild($avatar_bg);
			if($this->getAvatarType() !== AVATAR_TYPE_PREVIEW){
				$indicator = new OnlineStatusIndicator($mode);
				$indicator->bindContext($context);
				$this->appendChild($indicator);
			}
			return $this->hasChildNodes() ? $this->getChildNodes() : [];
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->avatarType, $deallocate);
	}

	public static function getTemplateContextClass(): string{
		return \JulianSeymour\PHPWebApplicationFramework\template\TemplateUser::class;
	}
}

<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\avatar;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\online\OnlineStatusIndicator;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\control\NodeBearingIfCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetForeignDataStructureCommand;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateElementInterface;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\style\StyleSheetPathTrait;

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
		return $this->avatarType = $type;
	}

	public function getAvatarType(){
		return $this->avatarType;
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
			if($this->getAvatarType() === AVATAR_TYPE_PREVIEW) {
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
			$avatar_bg->resolveTemplateCommand(NodeBearingIfCommand::if(CommandBuilder::and($context->hasForeignDataStructureCommand("profileImageKey"), $pid->hasColumnValueCommand("status")))->then($avatar_bg->appendChildCommand($thumb->bindElementCommand($pid))));
			$this->appendChild($avatar_bg);
			if($this->getAvatarType() !== AVATAR_TYPE_PREVIEW) {
				$indicator = new OnlineStatusIndicator($mode);
				$indicator->bindContext($context);
				$this->appendChild($indicator);
			}
			return $this->getChildNodes();
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->avatarType);
	}

	public static function getTemplateContextClass(): string{
		return \JulianSeymour\PHPWebApplicationFramework\template\TemplateUser::class;
	}
}

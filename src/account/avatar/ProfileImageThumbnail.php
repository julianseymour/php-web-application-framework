<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\avatar;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\control\BreakCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\Scope;
use JulianSeymour\PHPWebApplicationFramework\image\ImageElement;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateElementInterface;
use Exception;

class ProfileImageThumbnail extends ImageElement implements TemplateElementInterface{

	public static function isTemplateworthy(): bool{
		return true;
	}

	public static function getTemplateContextClass(): string{
		return ProfileImageData::class;
	}

	protected function getScopeResolutionCommands($context, Scope $scope):?array{
		$break = new BreakCommand();
		$translate = CommandBuilder::multiply($scope->getDeclaredVariableCommand("transform"), - 1);
		$translate->setParseType("px");
		return [
			$scope->let(
				"orig_height",
				new GetColumnValueCommand($context, "thumbnailHeight")
			),
			$scope->let(
				"orig_width",
				new GetColumnValueCommand($context, "thumbnailWidth")
			),
			$scope->let(
				"orientation",
				new GetColumnValueCommand($context, "orientation")
			),
			$scope->let(
				"focus",
				new GetColumnValueCommand($context, "focalLineRatio")
			),
			$scope->let("new_height"),
			$scope->let("new_width"),
			$scope->let("transform"),
			CommandBuilder::switch(
				$scope->getDeclaredVariableCommand("orientation")
			)->case(
				"landscape",
				$scope->redeclare("new_height", 50),
				$scope->redeclare(
					"new_width",
					CommandBuilder::multiply(
						CommandBuilder::divide(
							$scope->getDeclaredVariableCommand("orig_width"),
							$scope->getDeclaredVariableCommand("orig_height")
							),
						50
					)
				),
				$scope->redeclare(
					"transform",
					CommandBuilder::subtract(
						CommandBuilder::multiply(
							$scope->getDeclaredVariableCommand("new_width"),
							$scope->getDeclaredVariableCommand("focus")
						),
						25
					)
				),
				$this->setStylePropertiesCommand([
					"transform" => new ConcatenateCommand("translateX(", $translate, "px)")
				]),
				$break
			)->case(
				"portrait",
				$scope->redeclare("new_width", 50),
				$scope->redeclare(
					"new_height",
					CommandBuilder::multiply(
						CommandBuilder::divide(
							$scope->getDeclaredVariableCommand("orig_height"),
							$scope->getDeclaredVariableCommand("orig_width")
						),
						50
					)
				),
				$scope->redeclare(
					"transform",
					CommandBuilder::subtract(
						CommandBuilder::multiply(
							$scope->getDeclaredVariableCommand("new_height"),
							$scope->getDeclaredVariableCommand("focus")
						),
						25
					)
				),
				$this->setStylePropertiesCommand([
					"transform" => new ConcatenateCommand("translateY(", $translate, "px)")
				]),
				$break
			)->case(
				"square",
				$scope->redeclare("new_height", 50),
				$scope->redeclare("new_width", 50),
				$break
			)->default(
				CommandBuilder::return(
					new CallFunctionCommand("error", "f", "'Invalid orientation'")
				)
			)
		];
	}
	
	public function bindContext($context){
		$f = __METHOD__;
		try{
			$ret = parent::bindContext($context);
			$scope = $this->getResolvedScope();
			// set src attribute
			$this->setSourceAttribute(new GetColumnValueCommand($context, "webThumbnailPath"));
			// set dimensions
			$this->setStyleProperties([
				"height" => new ConcatenateCommand(
					"", 
					$scope->getDeclaredVariableCommand("new_height"), 
					"px"
				),
				"width" => new ConcatenateCommand(
					"", 
					$scope->getDeclaredVariableCommand("new_width"), 
					"px"
				)
			]);
			return $ret;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}

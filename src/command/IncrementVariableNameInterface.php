<?php
namespace JulianSeymour\PHPWebApplicationFramework\command;

/**
 * Needed to assign automatically generated variable names to generated nodes in template script.
 * This would be part of NodeBearingCommandInterface,
 * but BindElementCommand fucks up if it implements NodeBearingCommandInterface
 *
 * @author j
 *        
 */
interface IncrementVariableNameInterface
{

	/**
	 *
	 * @param int $counter
	 */
	public function incrementVariableName(int &$counter);
}

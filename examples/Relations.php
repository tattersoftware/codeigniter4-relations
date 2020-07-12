<?php namespace Config;

/***
*
* This file contains example values to alter default library behavior.
* Recommended usage:
*	1. Copy the file to app/Config/
*	2. Change any values
*	3. Remove any lines to fallback to defaults
*
***/

class Relations extends \Tatter\Relations\Config\Relations
{
	// Whether to continue instead of throwing exceptions
	public $silent = true;
	
	// Whether related items can load their own relations
	public $allowNesting = true;
	
	// Return type to fall back to if no model is available
	public $defaultReturnType = 'object';
}

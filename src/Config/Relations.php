<?php namespace Tatter\Schemas\Config;

use CodeIgniter\Config\BaseConfig;

class Schemas extends BaseConfig
{
	// Whether to continue instead of throwing exceptions
	public $silent = true;
	
	// Return type to fall back to if no model is available
	public $defaultReturnType = 'object';
}

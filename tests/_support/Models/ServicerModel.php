<?php namespace CIModuleTests\Support\Models;

use Tatter\Relations\Model;

class ServicerModel extends Model
{
	protected $table      = 'servicers';
	protected $primaryKey = 'id';

	protected $returnType = 'object';
	protected $useSoftDeletes = true;

	protected $allowedFields = ['company'];

	protected $useTimestamps = true;

	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = false;
	
	protected $with = ['lawyers'];
}

<?php namespace CIModuleTests\Support\Models;

use Tatter\Relations\Model;

class WorkerModel extends Model
{
	protected $table      = 'workers';
	protected $primaryKey = 'id';

	protected $returnType = 'object';
	protected $useSoftDeletes = true;

	protected $allowedFields = ['firstname', 'lastname', 'role', 'age'];

	protected $useTimestamps = true;

	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = false;
}

<?php namespace CIModuleTests\Support\Models;

use CodeIgniter\Model;

class WorkerModel extends Model
{
	use \Tatter\Relations\Traits\ModelTrait;
	 
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

<?php namespace Tests\Support\Models;

use Tatter\Relations\Traits\ModelTrait;
use CodeIgniter\Model;

class WorkerModel extends Model
{
	use ModelTrait;
	 
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

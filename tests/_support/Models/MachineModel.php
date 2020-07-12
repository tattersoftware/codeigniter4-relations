<?php namespace Tests\Support\Models;

use CodeIgniter\Model;

class MachineModel extends Model
{
	use \Tatter\Relations\Traits\ModelTrait;

	protected $table      = 'machines';
	protected $primaryKey = 'id';

	protected $returnType = 'object';
	protected $useSoftDeletes = false;

	protected $allowedFields = ['type', 'serial', 'factory_id'];

	protected $useTimestamps = true;

	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = false;
	
	protected $with = ['factories'];
}

<?php namespace Tests\Support\Models;

use Tatter\Relations\Traits\ModelTrait;
use CodeIgniter\Model;

class MachineModel extends Model
{
	use ModelTrait;

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

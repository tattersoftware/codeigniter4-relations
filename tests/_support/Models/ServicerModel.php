<?php namespace Tests\Support\Models;

use Tatter\Relations\Traits\ModelTrait;
use CodeIgniter\Model;

class ServicerModel extends Model
{
	use ModelTrait;

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

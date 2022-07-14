<?php namespace Tests\Support\Models;

use Tatter\Relations\Traits\ModelTrait;
use CodeIgniter\Model;

class LawyerModel extends Model
{
	use ModelTrait;

	protected $table      = 'lawyers';
	protected $primaryKey = 'id';

	protected $returnType = 'array';
	protected $useSoftDeletes = true;

	protected $allowedFields = ['servicer_id', 'name'];

	protected $useTimestamps = true;

	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = false;
}

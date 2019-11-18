<?php namespace CIModuleTests\Support\Models;

use CodeIgniter\Model;
use Tatter\Relations\Interfaces\RelatableInterface;

class LawyerModel extends Model implements RelatableInterface
{
	use \Tatter\Relations\Traits\ModelTrait;

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

<?php namespace CIModuleTests\Support\Models;

use CodeIgniter\Model;
use Tatter\Relations\Interfaces\RelatableInterface;

class FactoryModel extends Model implements RelatableInterface
{
	use \Tatter\Relations\Traits\ModelTrait;

	protected $table      = 'factories';
	protected $primaryKey = 'id';

	protected $returnType = 'object';
	protected $useSoftDeletes = true;

	protected $allowedFields = ['name', 'uid', 'class', 'icon', 'summary'];

	protected $useTimestamps = true;

	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = false;
}

<?php namespace CIModuleTests\Support\Models;

use Tatter\Relations\Model;

class LawyerModel extends Model
{
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

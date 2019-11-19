<?php namespace CIModuleTests\Support\Models;

use CodeIgniter\Model;

class ArrayModel extends Model
{
	protected $table      = 'factories';
	protected $primaryKey = 'id';

	protected $returnType = 'array';
	protected $useSoftDeletes = true;

	protected $allowedFields = ['name', 'uid', 'class', 'icon', 'summary'];

	protected $useTimestamps = true;

	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = false;
}

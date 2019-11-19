<?php namespace CIModuleTests\Support\Entities;

use CodeIgniter\Entity;

class Servicer extends Entity
{
	use \Tatter\Relations\Traits\EntityTrait;

	protected $table = 'servicers';

	protected $primaryKey = 'id';
	
	protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}

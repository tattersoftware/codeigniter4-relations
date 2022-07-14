<?php namespace Tests\Support\Entities;

use CodeIgniter\Entity\Entity;

class Servicer extends Entity
{
	use \Tatter\Relations\Traits\EntityTrait;

	protected $table = 'servicers';

	protected $primaryKey = 'id';
	
	protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}

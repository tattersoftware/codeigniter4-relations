<?php namespace Tests\Support\Entities;

use CodeIgniter\Entity\Entity;

class Factory extends Entity
{
	use \Tatter\Relations\Traits\EntityTrait;

	protected $table = 'factories';

	protected $primaryKey = 'id';
	
	protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}

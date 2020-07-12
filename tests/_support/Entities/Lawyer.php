<?php namespace Tests\Support\Entities;

use CodeIgniter\Entity;

class Lawyer extends Entity
{
	use \Tatter\Relations\Traits\EntityTrait;

	protected $table = 'lawyers';

	protected $primaryKey = 'id';
	
	protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}

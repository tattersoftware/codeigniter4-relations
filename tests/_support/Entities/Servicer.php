<?php namespace Tests\Support\Entities;

use Tatter\Relations\Traits\EntityTrait;
use CodeIgniter\Entity\Entity;

class Servicer extends Entity
{
	use EntityTrait;

	protected $table = 'servicers';

	protected $primaryKey = 'id';
	
	protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}

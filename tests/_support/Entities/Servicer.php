<?php

namespace Tests\Support\Entities;

use CodeIgniter\Entity\Entity;
use Tatter\Relations\Traits\EntityTrait;

class Servicer extends Entity
{
    use EntityTrait;

    protected $table      = 'servicers';
    protected $primaryKey = 'id';
    protected $dates      = ['created_at', 'updated_at', 'deleted_at'];
}

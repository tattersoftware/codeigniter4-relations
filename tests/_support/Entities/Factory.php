<?php

namespace Tests\Support\Entities;

use CodeIgniter\Entity\Entity;
use Tatter\Relations\Traits\EntityTrait;

class Factory extends Entity
{
    use EntityTrait;

    protected $table      = 'factories';
    protected $primaryKey = 'id';
    protected $dates      = ['created_at', 'updated_at', 'deleted_at'];
}

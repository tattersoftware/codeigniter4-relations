<?php

namespace Tatter\Relations\Config;

use CodeIgniter\Config\BaseConfig;

class Relations extends BaseConfig
{
    // Whether to continue instead of throwing exceptions
    public $silent = true;

    // Whether related items can load their own relations
    public $allowNesting = true;

    // Return type to fall back to if no model is available
    public $defaultReturnType = 'object';
}

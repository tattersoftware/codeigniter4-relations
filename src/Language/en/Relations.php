<?php

namespace Tatter\Relations\Language\en;

return [
    'noSchemas'        => 'Unable to load Schemas library!',
    'invalidWithout'   => '"Without" parameter must be a table name or array of names',
    'unknownTable'     => 'Table not present in schema: {0}',
    'unknownRelation'  => 'Table {0} is not known to be related to {1}',
    'missingPivots'    => 'Table {0} does not indicate a pivot route to {1}',
    'missingProperty'  => 'Class {0} must have the {1} property to use relations',
    'notRelatable'     => 'Class {0} must implement RelatableInterface to use relations',
    'invalidOperation' => 'Operation {0} not valid on {1}',
];

<?php

namespace App\Model;

use App\Traits\HasValidationTrait;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Respect\Validation\Validator;

/**
 * Base model for
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class AbstractModel extends EloquentModel
{
    use HasValidationTrait;
}

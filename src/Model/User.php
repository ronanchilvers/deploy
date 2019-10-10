<?php

namespace App\Model;

use App\Model\Finder\UserFinder;
use Respect\Validation\Validator;
use Ronanchilvers\Orm\Model;
use Ronanchilvers\Orm\Traits\HasValidationTrait;
use Ronanchilvers\Utility\Str;

/**
 * Model representing a project
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class User extends Model
{
    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';

    use HasValidationTrait;

    static protected $finder       = UserFinder::class;
    static protected $columnPrefix = 'user';

    protected $data = [
        'user_status' => 'active',
    ];

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function boot()
    {
        $this->addType('array', 'preferences');
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setupValidation()
    {
        $this->registerRules([
            'name'     => Validator::notEmpty(),
            'email'    => Validator::notEmpty()->email(),
            'password' => Validator::notEmpty(),
            'status'   => Validator::notEmpty()->in([static::STATUS_INACTIVE, static::STATUS_ACTIVE]),
        ]);
    }

    /**
     * Get a preference by key
     *
     * @param string $key
     * @param mixed $default
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function preference($key, $default = null)
    {
        $preferences = $this->preferences;
        if (is_null($preferences)) {
            return $default;
        }
        if (!isset($preferences[$key])) {
            return $default;
        }

        return $preferences[$key];
    }

    /**
     * Set a preference value for the user
     *
     * @param $key
     * @param mixed $value
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setPreference($key, $value)
    {
        $preferences       = $this->preferences;
        $preferences[$key] = $value;
        $this->preferences = $preferences;

        return $this->save();
    }

    /**
     * Verify a password against this user
     *
     * @param string $password
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function verify($password)
    {
        if (!$this->isLoaded()) {
            return false;
        }

        return password_verify($password, $this->password);
    }
}

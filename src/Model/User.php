<?php

namespace App\Model;

use App\Model\Finder\UserFinder;
use Carbon\Carbon;
use Respect\Validation\Validator;
use Ronanchilvers\Orm\Model;
use Ronanchilvers\Orm\Traits\HasValidationTrait;
use Ronanchilvers\Utility\Str;

/**
 * Model representing a project
 *
 * @property int id
 * @property string name
 * @property string email
 * @property string password
 * @property string status
 * @property null|string preferences
 * @property string level
 * @property null|\Carbon\Carbon last_login
 * @property null|\Carbon\Carbon created
 * @property null|\Carbon\Carbon updated
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class User extends Model
{
    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_PENDING  = 'pending';

    const LEVEL_USER      = 'user';
    const LEVEL_ADMIN     = 'admin';

    use HasValidationTrait;

    static protected $finder       = UserFinder::class;
    static protected $columnPrefix = 'user';

    protected $data = [
        'user_status' => 'pending',
        'user_level'  => 'user',
    ];

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function boot()
    {
        $this->addType('datetime', 'last_login');
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
            'status'   => Validator::notEmpty()->in([
                static::STATUS_PENDING,
                static::STATUS_INACTIVE,
                static::STATUS_ACTIVE,
            ]),
            'level'   => Validator::notEmpty()->in([
                static::LEVEL_USER,
                static::LEVEL_ADMIN,
            ]),
        ]);
        $this->registerRules([
            'password' => Validator::notEmpty(),
        ], 'security');
    }

    /**
     * Get the user level options for this user
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getLevelOptions(): array
    {
        return [
            static::LEVEL_USER => static::LEVEL_USER,
            static::LEVEL_ADMIN => static::LEVEL_ADMIN,
        ];
    }

    /**
     * Get the status options for this user
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getStatusOptions(): array
    {
        return [
            static::STATUS_INACTIVE => static::STATUS_INACTIVE,
            static::STATUS_ACTIVE   => static::STATUS_ACTIVE,
        ];
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

    /**
     * Verify and set a new password
     *
     * @param string $old
     * @param string $new
     * @param string $confirm
     * @return bool
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setNewPassword(string $old, string $new, string $confirm)
    {
        foreach (['old', 'new', 'confirm'] as $var) {
            $$var = trim($$var);
        }
        if (!$this->verify($old)) {
            return false;
        }

        if (empty($new)) {
            return false;
        }

        if ($new !== $confirm) {
            return false;
        }

        $this->password = password_hash($new, PASSWORD_DEFAULT);

        return true;
    }

    /**
     * Record a last login timestamp
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function recordLogin(): bool
    {
        $this->last_login = Carbon::now();

        return $this->save();
    }
}

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
    const STATUS_INVITED  = 'invited';
    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';

    const LEVEL_USER      = 'user';
    const LEVEL_ADMIN     = 'admin';

    use HasValidationTrait;

    static protected $finder       = UserFinder::class;
    static protected $columnPrefix = 'user';

    protected $data = [
        'user_status' => 'invited',
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
                static::STATUS_INVITED,
                static::STATUS_INACTIVE,
                static::STATUS_ACTIVE,
            ]),
            'level'   => Validator::notEmpty()->in([
                static::LEVEL_USER,
                static::LEVEL_ADMIN,
            ]),
        ]);

        $this->registerRules([
            'name'     => Validator::notEmpty(),
            'email'    => Validator::notEmpty()->email(),
        ], 'invitation');

        $this->registerRules([
            'password' => Validator::notEmpty(),
        ], 'security');
    }

    /**
     * Override for beforeCreate model hook
     */
    public function beforeCreate(): void
    {
        $this->hash = Str::token(32);
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
    public function verify(string $password): bool
    {
        if (!$this->isLoaded()) {
            return false;
        }

        return password_verify($password, $this->password);
    }

    /**
     * Set the password for this user
     *
     * @param string $password
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setPassword(string $password, string $confirm = null): bool
    {
        if (empty($password)) {
            return false;
        }
        $password = trim($password);
        if (!is_null($confirm) && trim($confirm) != $password) {
            $this->addError('password', 'Password does not match confirmation');
            return false;
        }

        $this->password = password_hash($password, PASSWORD_DEFAULT);

        return true;
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
    public function setNewPassword(string $old, string $new, string $confirm): bool
    {
        foreach (['old', 'new', 'confirm'] as $var) {
            $$var = trim($$var);
        }
        if (!$this->verify($old)) {
            return false;
        }

        return $this->setPassword($new, $confirm);
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

    /**
     * Is this user active?
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isActive(): bool
    {
        return $this->status == static::STATUS_ACTIVE;
    }

    /**
     * Is this user inactive?
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isInvited(): bool
    {
        return $this->status == static::STATUS_INVITED;
    }

    /**
     * Is this user an admin?
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isAdmin(): bool
    {
        return static::LEVEL_ADMIN == $this->level;
    }

    /**
     * Activate this user
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function activate()
    {
        $this->status = static::STATUS_ACTIVE;
    }

    /**
     * Deactivate this user
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function deactivate()
    {
        $this->status = static::STATUS_INACTIVE;
    }
}

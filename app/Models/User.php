<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nickname',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationship to Play class
    public function plays()
    {
        return $this->hasMany(Play::class);
    }

    /*
     * Validate nickname uniqueness if not "anonymous".
     * This method ensures that before creating or updating a User instance, the nickname field is checked for uniqueness with the
     * following rule: Custom nicknames (any value other than "anonymous") must be unique.
     * The saving event covers both creating and updating actions, so so there is no need to duplicate logic for both cases.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($user) {
            if ($user->nickname !== 'anonymous') {
                $exists = User::where('nickname', $user->nickname)
                    ->where('id', '!=', $user->id) // Exclude the current user for updates
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'nickname' => 'The nickname must be unique unless it is "anonymous".',
                    ]);
                }
            }
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPlayer(): bool
    {
        return $this->role === 'player';
    }

}

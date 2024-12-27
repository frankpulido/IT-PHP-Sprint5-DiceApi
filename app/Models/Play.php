<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // This was added by me
use Illuminate\Database\Eloquent\Casts\Attribute;  // This was added by me


class Play extends Model
{
    use HasFactory;
    protected $table = 'plays'; // This was added by me (not really necessary because I used class plural)
    protected $fillable = [ // These are the only attributes that can be mass-assigned by faker
        'user_id',
        'dice1',
        'dice2',
        'success',
    ];

    protected $casts = [ // Attribute casting
        'user_id' => 'integer',
        'dice1' => 'integer',
        'dice2' => 'integer',
        'success' => 'boolean',
    ];
    
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($play) {
            $play->success = ($play->dice1 + $play->dice2) === 7;
        });
    }

    // Relationship to Section
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

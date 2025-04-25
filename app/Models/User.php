<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'division',
        'phone_number',
        'password',
        'avatar',
    ];

    public function createTokenWithExpiry($name, $scopes = [])
    {
        // Atur expire date ke 1 hari
        $tokenResult = $this->createToken($name, $scopes);
        $token = $tokenResult->token;
        $token->expires_at = now()->addDay();
        $token->save();

        return $tokenResult;
    }

    public function ticket()
    {
        return $this->hasMany(Ticket::class, 'id_user', 'id');
    }

    public function quotation()
    {
        return $this->hasMany(Quotation::class, 'id_user', 'id');
    }

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
}

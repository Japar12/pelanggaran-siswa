<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles; // 🟢 tambahkan ini
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone_number',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
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

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function class_room()
    {
        return $this->hasMany(ClassRoom::class, 'teacher_id');
    }

    public function student()
{
    return $this->hasOne(Student::class);
}

public function students()
{
    // untuk role ortu (bisa punya beberapa anak)
    return $this->hasMany(Student::class, 'parent_user_id');
}

public function studentProfile()
{
    return $this->hasOne(Student::class, 'user_id');
}

protected static function booted()
{
    static::created(function ($user) {
        if ($user->hasRole('siswa')) {
            Student::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'nisn' => fake()->unique()->numerify('##########'),
            ]);
        }
    });
}

 public function canAccessPanel(Panel $panel): bool
    {
        // izinkan role-role ini menerima notifikasi web
        return $this->hasAnyRole(['admin', 'guru', 'siswa', 'ortu']);
    }
}

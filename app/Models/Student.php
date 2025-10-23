<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nisn',
        'class_room_id',
        'user_id',
    ];

    public function class_room()
    {
        return $this->belongsTo(ClassRoom::class, 'class_room_id');
    }

    public function violations()
    {
        return $this->hasMany(Violation::class);
    }

    public function updateTotalPoints(): void
    {
        $this->total_points = $this->violations()->sum('points');
        $this->save();
    }

    public function getViolationStats(): array
{
    $total = $this->violations()->count();
    $heavy = $this->violations()->where('category', 'Berat')->count();

    return [
        'total' => $total,
        'heavy' => $heavy,
        'heavy_percent' => $total ? round(($heavy / $total) * 100, 2) : 0,
    ];
}

public function user()
{
    return $this->belongsTo(User::class);
}

protected static function booted()
{
    static::creating(function ($student) {
        if ($student->user && !$student->name) {
            $student->name = $student->user->name;
        }
    });
}

public function parent()
{
    return $this->belongsTo(User::class, 'parent_user_id');
}
}

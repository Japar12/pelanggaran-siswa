<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Violation extends Model
{
    use HasFactory;

    protected $fillable = [
       'student_id',
        'description',
        'points',
        'category',
        'date',
        'status',
        'created_by',

    ];


    // 🟢 Eager load student dan class_room otomatis agar tidak query berulang
    protected $with = ['student.class_room'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    protected static function booted()
    {
        // Saat data baru dibuat, simpan siapa yang membuat (guru/admin)
            static::creating(function ($violation) {
                if (auth()->check()) {
                    $violation->created_by = auth()->id();
                }
            });

        static::saving(function ($violation) {
            if ($violation->points <= 20) {
                $violation->category = 'Ringan';
            } elseif ($violation->points <= 50) {
                $violation->category = 'Sedang';
            } else {
                $violation->category = 'Berat';
            }

        });

        static::saved(fn ($v) => $v->student->updateTotalPoints());
        static::deleted(fn ($v) => $v->student->updateTotalPoints());
    }

    public function createdBy()
{
    return $this->belongsTo(User::class, 'created_by');
}


}

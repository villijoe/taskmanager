<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'due_date', 'category_id', 'user_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Добавление кастомного атрибута к массиву атрибутов модели
    protected $appends = ['status'];

    public function getStatusAttribute()
    {
        // Сравнение текущей даты с датой выполнения задачи
        if ($this->due_date && $this->due_date < now()) {
            return 'DONE';
        }

        return 'IN_PROGRESS';
    }
}

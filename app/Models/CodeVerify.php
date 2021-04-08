<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeVerify extends Model
{
    use HasFactory;

    protected $table = 'code_verifies';
    protected $fillable = ['code_sms'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

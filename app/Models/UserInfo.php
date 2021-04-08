<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    use HasFactory;

    protected $table = 'user_info';
    protected $fillable = ['menkyou_number','ryouka', 'workplace_name', 'occupation', 'dob','university_name', 'year_graduated'];

    protected $casts = [
        'ryouka' => 'array'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

               
}

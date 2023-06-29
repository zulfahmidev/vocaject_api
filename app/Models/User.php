<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getDetail() {
        if ($this->role == 'student') {
            return DB::table('users')
            ->selectRaw('users.id as id, name, email, nim, address, phone, college_id, role')
            ->join('student_details', 'users.id', '=', 'student_details.user_id')
            ->where('users.id', $this->id)
            ->first();
        }else if($this->role == 'lecture') {
            return DB::table('users')
            ->selectRaw('users.id as id, name, email, nidn, address, phone, college_id, role')
            ->join('lecture_details', 'users.id', '=', 'lecture_details.user_id')
            ->where('users.id', $this->id)
            ->first();
        }else if(in_array($this->role, ['college', 'company'])) {
            return DB::table('users')
            ->selectRaw('users.id as id, name, email, description, address, phone, role')
            ->join('company_details', 'users.id', '=', 'company_details.user_id')
            ->where('users.id', $this->id)
            ->first();
        }
        return null;
    }
}

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
        'balance',
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
            $user = DB::table('users')
            ->selectRaw('users.id as id, name, email, 
                CASE WHEN picture IS NULL THEN "'.getUrl('/images/default.jpeg').'" ELSE CONCAT("'.getUrl('/').'/uploads/", picture) END as picture,
                nim, balance, address, phone, college_id, role, status, users.created_at, users.updated_at')
            ->join('student_details', 'users.id', '=', 'student_details.user_id')
            ->where('users.id', $this->id)
            ->first();
            $user->college = User::find($user->college_id)->getDetail();
            unset($user->college_id);
            return $user;
        }else if($this->role == 'lecture') {
            $user = DB::table('users')
            ->selectRaw('users.id as id, name, email, 
                CASE WHEN picture IS NULL THEN "'.getUrl('/images/default.jpeg').'" ELSE CONCAT("'.getUrl('/').'/uploads/", picture) END as picture,
                nidn, balance, address, phone, college_id, role, status, users.created_at, users.updated_at')
            ->join('lecture_details', 'users.id', '=', 'lecture_details.user_id')
            ->where('users.id', $this->id)
            ->first();
            $user->college = User::find($user->college_id)->getDetail();
            unset($user->college_id);
            return $user;
        }else if(in_array($this->role, ['college', 'company'])) {
            return DB::table('users')
            ->selectRaw('users.id as id, name, email, 
                CASE WHEN picture IS NULL THEN "'.getUrl('/images/default.jpeg').'" ELSE CONCAT("'.getUrl('/').'/uploads/", picture) END as picture,
                description, balance, address, phone, role, status, users.created_at, users.updated_at')
            ->join('company_details', 'users.id', '=', 'company_details.user_id')
            ->where('users.id', $this->id)
            ->first();
        }
        return null;
    }

    public static function getStudents() {
        $users = DB::table('users')
        ->selectRaw('users.id as id, name, email, 
            CASE WHEN picture IS NULL THEN "'.getUrl('/images/default.jpeg').'" ELSE CONCAT("'.getUrl('/').'/uploads/", picture) END as picture,
            nim, balance, address, phone, college_id, role, status, users.created_at, users.updated_at')
        ->join('student_details', 'users.id', '=', 'student_details.user_id')
        ->get();
        foreach ($users as $user) {
            $user->college = User::find($user->college_id)->getDetail();
            unset($user->college_id);
        }
        return $users;
    }

    public static function getLectures() {
        $users = DB::table('users')
        ->selectRaw('users.id as id, name, email, 
            CASE WHEN picture IS NULL THEN "'.getUrl('/images/default.jpeg').'" ELSE CONCAT("'.getUrl('/').'/uploads/", picture) END as picture,
            nidn, balance, address, phone, college_id, role, status, users.created_at, users.updated_at')
        ->join('lecture_details', 'users.id', '=', 'lecture_details.user_id')
        ->get();
        foreach ($users as $user) {
            $user->college = User::find($user->college_id)->getDetail();
            unset($user->college_id);
        }
        return $users;
    }

    public static function getColleges() {
        return DB::table('users')
        ->selectRaw('users.id as id, name, email, 
            CASE WHEN picture IS NULL THEN "'.getUrl('/images/default.jpeg').'" ELSE CONCAT("'.getUrl('/').'/uploads/", picture) END as picture,
            description, balance, address, phone, role, status')
        ->join('company_details', 'users.id', '=', 'company_details.user_id')
        ->where('role', 'college')
        ->get();
    }

    public static function getCompanies() {
        return DB::table('users')
        ->selectRaw('users.id as id, name, email, 
            CASE WHEN picture IS NULL THEN "'.getUrl('/images/default.jpeg').'" ELSE CONCAT("'.getUrl('/').'/uploads/", picture) END as picture,
            description, balance, address, phone, role, status')
        ->join('company_details', 'users.id', '=', 'company_details.user_id')
        ->where('role', 'company')
        ->get();
    }
}

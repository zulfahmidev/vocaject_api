<?php

namespace App\Http\Controllers;

use App\Models\CompanyDetail;
use App\Models\LectureDetail;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\StudentDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InitController extends Controller
{
    public static function initUsers() {

        $file = file_get_contents(public_path('data.json'));
        $data = json_decode($file, true);
        // dd($data);

        foreach ($data['users'] as $v) {
            $user = User::create([
                "name" => $v['name'],
                "email" => $v['email'],
                "role" => $v['role'],
                "balance" => 0,
                "password" => Hash::make("vocaject")
            ]);

            if ($user->role == 'college' || $user->role == 'company') {
                CompanyDetail::create([
                    'user_id' => $user->id,
                    'description' => trim($v['description']),
                    'address' => strtolower(trim($v['address'])),
                    'phone' => trim($v['phone']),
                ]);
            }elseif ($user->role == 'lecture') {
                $college_ids = User::where('role', 'college')->pluck('id');
                $rand_college_id = rand(0, count($college_ids)-1);
                LectureDetail::create([
                    'user_id' => $user->id,
                    'nidn' => trim($v['nidn']),
                    'address' => strtolower(trim($v['address'])),
                    'phone' => trim($v['phone']),
                    'college_id' => $college_ids[$rand_college_id],
                ]);
            }elseif ($user->role == 'student') {
                $college_ids = User::where('role', 'college')->pluck('id');
                $rand_college_id = rand(0, count($college_ids)-1);
                StudentDetail::create([
                    'user_id' => $user->id,
                    'nim' => trim($v['nim']),
                    'address' => strtolower(trim($v['address'])),
                    'phone' => trim($v['phone']),
                    'college_id' => $college_ids[$rand_college_id],
                ]);
            }
        }

    }

    public static function initProjects() {
        
        $file = file_get_contents(public_path('data.json'));
        $data = json_decode($file, true);

        foreach ($data['categories'] as $v) {
            ProjectCategory::create([
                "name" => $v['name'],
                "slug" => $v['slug'],
            ]);
        }

        foreach ($data['projects'] as $p) {
            $company_ids = User::where('role', 'company')->pluck('id');
            $rand_company_id = rand(0, count($company_ids)-1);
            Project::create([
                "company_id" => $company_ids[$rand_company_id],
                "title" => $p['title'],
                "description" => $p['description'],
                "expired_at" => self::timestampFormat("06-06-2024"),
                "deadline_at" => self::timestampFormat("10-06-2024"),
                "budget" => $p['budget'],
                "category_id" => $p['category_id'],
            ]);
        }
    }

    private static function timestampFormat($date) {
        $date = explode('-', $date);
        return Carbon::create($date[2], $date[0], $date[1]);
    }
}

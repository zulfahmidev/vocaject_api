<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Document extends Model
{
    use HasFactory;

    public $fillable = [
        "filename", "origin_filename", "mimetype", "extension", "visibility"
    ];

    public function getData() {
        $data = $this->toArray();
        $url = '/api/document/view/'.$this->filename;
        $data['url'] = (env('APP_ENV') == 'local') ? url($url) : secure_url($url);
        return Collection::make($data);
    }
}

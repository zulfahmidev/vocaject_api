<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    public $fillable = [
        "filename", "origin_filename", "mimetype", "extension", "visibility"
    ];

    public function getData() {
        $data = $this->toArray();
        $data['url'] = url('/api/document/view/'.$this->filename);
        return Collection::make($data);
    }
}
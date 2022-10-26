<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FontAwesomeGlyph extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    public function getPreviewSvgAttribute()
    {
        return $this->svg ?
            (string) Str::of($this->svg)
                ->replace('path', 'path fill="currentColor"')
                ->replaceFirst('svg', 'svg class="h-full w-full"')
            : <<<HTML
        <i class="fa-$this->style fa-$this->id h-full w-full"></i>
        HTML;
    }
}

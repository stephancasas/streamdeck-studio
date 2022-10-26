<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedQuery extends Model
{
    use HasFactory;

    protected $fillable = ['keywords', 'results'];

    protected $casts = [
        'results' => 'json',
    ];

    public function getGlyphsAttribute()
    {
        return collect($this->results)
            ->map(fn ($result) => FontAwesomeGlyph::firstOrNew($result));
    }
}

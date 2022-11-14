<?php

namespace App\Http\Livewire;

use App\Facades\FontAwesome;
use App\Models\FontAwesomeGlyph;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class GlyphSearch extends Component
{
    public Collection $results;

    public array $placeholders;

    public function mount()
    {
        $this->useDefaultResults();
        $this->placeholders = $this->getRandomGlyphs()
            ->map(
                fn ($glyph) => Str::of($glyph->id)
                    ->headline()
                    ->words(1, '')
                    ->lower()
            )->toArray();
    }

    public function useDefaultResults()
    {
        $this->results = $this->getRandomGlyphs()
            ->mapWithKeys(fn ($glyph) => [$glyph->id => $glyph->preview_svg]);
    }

    public function getRandomGlyphs()
    {
        return FontAwesomeGlyph::inRandomOrder()
            ->whereNotNull('svg')
            ->where('style', 'solid')
            ->limit(30)
            ->get();
    }

    public function search($query = '')
    {
        $this->results = collect();

        if (blank($query)) {
            return $this->useDefaultResults();
        }

        $this->results = FontAwesome::search($query)
            ->mapWithKeys(fn ($glyph) => [$glyph->id => $glyph->preview_svg]);
    }

    public function chooseGlyph($glyphId)
    {
        $this->emitTo('icon-editor', 'chooseGlyph', $glyphId);
    }

    public function render()
    {
        return view('livewire.glyph-search');
    }
}

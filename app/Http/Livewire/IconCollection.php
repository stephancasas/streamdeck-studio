<?php

namespace App\Http\Livewire;

use App\Models\Telemetry;
use Livewire\Component;

class IconCollection extends Component
{
    public function telemetry($collectionName = 'studio.streamdeck.blank', $icons = [])
    {
        (new Telemetry())->forceFill([
            'label' => $collectionName,
            'glyph' => 'studio.streamdeck.collection',
            'action' => 'collection-export-download',
            'options' => $icons,
        ])->save();
    }

    public function render()
    {
        return view('livewire.icon-collection');
    }
}

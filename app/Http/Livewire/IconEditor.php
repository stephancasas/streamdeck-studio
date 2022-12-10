<?php

namespace App\Http\Livewire;

use App\Models\FontAwesomeGlyph;
use App\Models\Telemetry;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;

class IconEditor extends Component
{
    public FontAwesomeGlyph $glyph;

    public string $label = '';

    public bool $labelVisibility = true;

    public bool $userModifiedLabel = false;

    public string $glyphColor = 'sky-400';

    public string $labelColor = 'white';

    public string $labelTypeface = 'VT323';

    public string $canvasColor = 'sky-600';

    public string $glyphColorLabel;

    public string $labelColorLabel;

    public string $canvasColorLabel;

    public bool $useAdvancedColorUi = false;

    public $colorScheme =
    'Canvas should be darker than glyph by 2 shades';

    const RANDOM_HUES = [
        'red',
        'orange',
        'amber',
        'yellow',
        'lime',
        'green',
        'emerald',
        'teal',
        'cyan',
        'sky',
        'blue',
        'indigo',
        'violet',
        'purple',
        'fuchsia',
        'pink',
        'rose',
    ];

    public function mount()
    {
        $hue = Arr::random(static::RANDOM_HUES);
        $this->glyphColor = "$hue-400";
        $this->canvasColor = "$hue-600";

        $this->loadGlyph();
        $this->setColorLabels();
    }

    protected $listeners = [
        'chooseGlyph' => 'loadGlyph',
        'load-icon-from-storage' => 'loadIconFromStorage',
    ];

    public function loadGlyph($glyphId = '')
    {
        if (empty($glyphId)) {
            $this->glyph = FontAwesomeGlyph::inRandomOrder()
                ->whereNotNull('svg')
                ->where('style', 'solid')
                ->first();
        } else {
            $this->glyph = FontAwesomeGlyph::find($glyphId);
        }

        if (!$this->userModifiedLabel) {
            $this->label = (string) Str::of($this->glyph->id)
                ->headline()
                ->words(2, '')
                ->limit(10);
        }
    }

    public function setColorLabels()
    {
        $this->glyphColorLabel = Str::headline($this->glyphColor);
        $this->labelColorLabel = Str::headline($this->labelColor);
        $this->canvasColorLabel = Str::headline($this->canvasColor);
    }

    public function updated($prop, $value)
    {
        if (Str::endsWith($prop, 'Color')) {
            $value = Str::lower($value);
            $this->{$prop} = $value;
        }

        if (!$this->useAdvancedColorUi) {
            $this->useColorScheme($prop, $value);
        }

        if ($prop !== 'label') {
            $this->setColorLabels();
        } else {
            $this->userModifiedLabel = true;
        }

        $this->emit('icon-did-update');
    }

    public function serializeIconOptions()
    {
        return [
            'glyph' => $this->glyph->id,
            'label' => $this->label,
            'label_visibility' => $this->labelVisibility,
            'label_color' => $this->labelColor,
            'label_typeface' => $this->labelTypeface,
            'glyph_color' => $this->glyphColor,
            'canvas_color' => $this->canvasColor,
        ];
    }

    public function collectIcon()
    {
        $this->telemetry('icon-collect-add');

        $this->dispatchBrowserEvent(
            'collect-editor-icon',
            $this->serializeIconOptions()
        );
    }

    public function loadIconFromStorage($detail)
    {
        $this->glyph = FontAwesomeGlyph::find($detail['glyph']);
        $this->label = $detail['label'];
        $this->labelVisibility = $detail['label_visibility'];
        $this->labelColor = $detail['label_color'];
        $this->labelTypeface = $detail['label_typeface'];
        $this->glyphColor = $detail['glyph_color'];
        $this->canvasColor = $detail['canvas_color'];
    }

    public function getSchemeOptions()
    {
        return [
            'None',
            'Canvas should be darker than glyph by 1 shade',
            'Canvas should be darker than glyph by 2 shades',
            'Glyph should be darker than canvas by 1 shade',
            'Glyph should be darker than canvas by 2 shades',
        ];
    }

    private function useColorScheme($changed, $toValue)
    {
        if (blank($this->colorScheme) || $this->colorScheme === 'None') {
            return;
        }

        if ((!Str::endsWith($changed, 'Color')) || Str::startsWith($changed, 'label')) {
            return;
        }

        $hue = (string) Str::of($toValue)->before('-')->lower();
        $shade = Str::of($toValue)->after('-')->toInteger();

        $shift = Str::of($this->colorScheme)
            ->afterLast('by')
            ->words(1, '')
            ->toInteger();
        $darker = (string) Str::of($this->colorScheme)
            ->words(1, '')
            ->trim()
            ->lower() . 'Color';

        $brighter = 'canvasColor';
        if ($darker === 'canvasColor') {
            $brighter = 'glyphColor';
        }

        if ($hue === 'white' || $hue === 'black') {
            $this->{$darker} = 'black';
            $this->{$brighter} = 'white';
        } else {
            if ($changed === $darker) {
                $shade = ($shade === 50 ? 100 : $shade) - (100 * $shift);

                if ($shade <= 0) {
                    $this->{$brighter} = "${hue}-50";
                    $shade = $shift === 1 ? 100 : 200;
                    $this->{$darker} = "${hue}-${shade}";
                } else {
                    $this->{$brighter} = "${hue}-{$shade}";
                }
            } else {
                $shade = ($shade === 50 ? 100 : $shade) + (100 * $shift);

                if ($shade > 900) {
                    $this->{$darker} = "${hue}-900";
                    $shade = $shift === 1 ? 800 : 700;
                    $this->{$brighter} = "${hue}-${shade}";
                } else {
                    $this->{$darker} = "${hue}-{$shade}";
                }
            }
        }
    }

    public function telemetry($action)
    {
        $options = collect($this->serializeIconOptions());
        $label = $options->get('label');
        $glyph = $options->get('glyph');

        $options->forget(['glyph', 'label']);

        (new Telemetry())
            ->forceFill(compact('options', 'label', 'glyph', 'action'))
            ->save();
    }

    public function render()
    {
        return view('livewire.icon-editor');
    }
}

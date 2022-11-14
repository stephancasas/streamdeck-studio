<?php

namespace App\Jobs;

use App\Facades\FontAwesome;
use App\Models\SavedQuery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSavedQuery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public SavedQuery $savedQuery;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SavedQuery $savedQuery)
    {
        $this->savedQuery = $savedQuery;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->savedQuery
            ->glyphs
            ->filterStr('svg', 'isEmpty')
            ->each(function ($glyph) {
                $glyph->svg = FontAwesome::svg($glyph);
                $glyph->save();
            });
    }
}

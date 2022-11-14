<?php

namespace App\Lib\FontAwesome;

use App\Jobs\ProcessSavedQuery;
use App\Lib\FontAwesome\Support\AnonymousCache;
use App\Models\FontAwesomeGlyph;
use App\Models\SavedQuery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FontAwesome
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = $this->config('api_key');
    }

    private function apiSearch($keywords)
    {
        $version = $this->config('version');

        $response = Http::withToken($this->getToken())
            ->post($this->route('graphql'), [
                'query' => 'query {'.
                    'search ('.
                    "version: \"$version\",".
                    "query: \"$keywords\"".
                    ') {id, styles}}',
            ])->json();

        $results = collect($response)
            ->then()->pull('data.search')
            ->map(function ($props) {
                $id = $props['id'];
                $styles = collect($props['styles']);
                $style = $styles->contains('solid') ?
                    'solid' : $styles->first();

                return ['id' => $id, 'style' => $style];
            });

        $savedQuery = new SavedQuery(compact('keywords', 'results'));
        $savedQuery->save();

        return $savedQuery;
    }

    public function search($keywords)
    {
        $keywords = (string) Str::of($keywords)
            ->lower()
            ->trim();

        $savedQuery = SavedQuery::query()
            ->where('keywords', '=', $keywords)
            ->firstOr(['results'], function () use ($keywords) {
                $savedQuery = $this->apiSearch($keywords);
                ProcessSavedQuery::dispatch($savedQuery);

                return $savedQuery;
            });

        return $savedQuery->glyphs;
    }

    public function svg(FontAwesomeGlyph $icon)
    {
        $version = $this->config('version');
        $assets = $this->config('assets_url');

        return Http::get("$assets/v$version/svgs/$icon->style/$icon->id.svg")
            ->body();
    }

    public function useGlyph($id, $style = 'solid')
    {
        $glyph = FontAwesomeGlyph::firstOrNew(['id' => $id, 'style' => $style]);

        return $glyph->preview_svg;
    }

    private function getToken()
    {
        if (! ($token = $this->cache('token')->get())) {
            ['access_token' => $token, 'expires_in' => $expiry] =
                Http::withToken($this->apiKey)
                ->post($this->route('token'))
                ->json();
            $this->cache('token')->put($token, $expiry);
        }

        return $token;
    }

    private function route($key)
    {
        return $this->config('base_url').
            $this->config("routes.$key");
    }

    private function cache($key)
    {
        $prefix = $this->config('cache_prefix');

        return new AnonymousCache($prefix, $key);
    }

    private function config($key)
    {
        return config("fontawesome.$key");
    }
}

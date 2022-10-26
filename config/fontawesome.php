<?php

return [

    /**
     * -------------------------------------------------------------------------
     * FontAwesome Version
     * -------------------------------------------------------------------------
     *
     * This is the version of FontAwesome to use in search queries.
     */
    'version' => env('FONTAWESOME_VERSION', '6.1.1'),

    /**
     * -------------------------------------------------------------------------
     * FontAwesome API Base URL
     * -------------------------------------------------------------------------
     *
     * This is the base url for the FontAwesome API.
     */
    'base_url' => env('FONTAWESOME_BASE_URL', 'https://api.fontawesome.com'),

    /**
     * -------------------------------------------------------------------------
     * FontAwesome API Key
     * -------------------------------------------------------------------------
     *
     * This is the API key for a paid FontAwesome subscription.
     */
    'api_key' => env('FONTAWESOME_KEY'),

    /**
     * -------------------------------------------------------------------------
     * FontAwesome API Routes
     * -------------------------------------------------------------------------
     *
     * These are the routes implemented in the FontAwesome service provider.
     */
    'routes' => [
        'token' => '/token',
        'graphql' => '/',
    ],

    /**
     * -------------------------------------------------------------------------
     * FontAwesome Assets URL
     * -------------------------------------------------------------------------
     *
     * This is the base url for retrieving FontAwesome assets -- like SVGs.
     */
    'assets_url' => env(
        'FONTAWESOME_ASSETS_URL',
        'https://site-assets.fontawesome.com/releases'
    ),

    /**
     * -------------------------------------------------------------------------
     * Cache Prefix
     * -------------------------------------------------------------------------
     *
     * The prefix added to all cache entries.
     */
    'cache_prefix' => env('FONTAWESOME_CACHE_PREFIX', 'fontawesome_'),
];

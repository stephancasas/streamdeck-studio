<?php

namespace App\Providers;

use App\Lib\FontAwesome\FontAwesome;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class FontAwesomeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            FontAwesome::class,
            fn () => new FontAwesome()
        );
    }

    public function boot()
    {
        Blade::directive(
            'fontawesome',
            static function (string $expression) {
                return app('fontawesome')
                    ->useGlyph(...static::evalBladeArg($expression));
            }
        );

        Blade::directive('square', static function () {
            return <<<'HTML'
            <svg>
                <rect width="1" height="1" style="fill:rgba(0,0,0,1);" />
            </svg>
            HTML;
        });
    }

    /**
     * Safely evaluate arguments passed to Blade directives.
     *
     * @param  mixed  $expression The plaintext PHP expression to evaluate.
     * @return mixed
     */
    protected static function evalBladeArg($expression)
    {
        return eval(<<<PHP
        return (fn(...\$expression) => \$expression)($expression);
        PHP);
    }
}

<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Hotdot
{
    public static function convert($target, ...$path)
    {
        $path = preg_split('/\./', collect($path)->join('.'));
        $accessor = collect();
        collect($path)->reduce(function ($acc, $cur) use ($accessor) {
            if (is_array($acc)) {
                $accessor->push("['$cur']");

                return $acc[$cur];
            }
            if ($acc instanceof Collection) {
                $accessor->push("->get('$cur')");

                return $acc->get($cur);
            }
            $accessor->push("->$cur");

            return $acc->{$cur};
        }, $target);

        return $accessor->join('');
    }

    public static function setter($model, ...$path)
    {
        $convert = Hotdot::convert($model, ...$path);

        return function ($target, $value) use ($convert) {
            eval('return $target'.$convert.' = $value;');

            return $target;
        };
    }

    public static function getter($model, ...$path)
    {
        $convert = Hotdot::convert($model, ...$path);

        return function ($target) use ($convert) {
            try {
                return eval('return $target'.$convert.';');
            } catch (Exception $ex) {
                return null;
            }
        };
    }

    public static function set($target, $value, ...$path)
    {
        return Hotdot::setter($target, ...$path)($target, $value);
    }

    public static function get($target, ...$path)
    {
        return Hotdot::getter($target, ...$path)($target);
    }
}

/**
 * Helper class to wrap the result of any collection method
 * inside of a new collection.
 */
class CollectionProxy
{
    public $collection;

    public $first;

    public function __construct($collection, ...$first)
    {
        $this->collection = $collection;
        $this->first = $first ?? [];
    }

    public function applyEach($method, $args)
    {
        return $this->collection
            ->map(
                fn ($item) => Collection::unwrap(collect($item)->{$method}(...$args))
            );
    }

    /**
     * Pass any calls to root collection object.
     */
    public function __call($method, $args)
    {
        return empty($this->first) ?
            collect($this->collection->{$method}(...$args)) :
            $this->{$this->first[0]}($method, $args);
    }
}

/**
 * After performing the next-chained method, get the result as a new
 * collection.
 */
Collection::macro('then', function () {
    return new CollectionProxy($this);
});

/**
 * Group the keys of a collection by their associated value, or by a shared
 * property name.
 */
Collection::macro('groupKeys', function ($by = null) {
    $target = empty($by) ? $this : $this->applyEach()->only($by);

    return $target
        ->transform(fn ($x, $y) => ['__VALUE__' => $x, '__KEY__' => $y])
        ->groupBy('__VALUE__')
        ->applyEach()->pluck('__KEY__');
});

/**
 * Apply the given named method of the `Str` helper class to each collection
 * item, and get the result as a new collection.
 */
Collection::macro('str', function (...$args) {
    if ($this->isEmpty()) {
        return $this;
    }

    // use `@` to modify keys
    if ($args[0] === '@') {
        $method = $args[1];
        $methodArgs = collect($args)
            ->forget([0, 1])
            ->toArray();

        return $this->mapWithKeys(fn ($x, $y) => [
            (string) Str::of($y)->{$method}(...$methodArgs) => $x,
        ]);
    }

    $model = $this->first();

    if (['array' => true, 'object' => true][gettype($model)] ?? false) {
        $getter = Hotdot::getter($model, $args[0]);
        $setter = Hotdot::setter($model, $args[0]);
        $transformer = fn ($str) => fn ($item) => $setter($item, $str($getter($item)));
        $method = $args[1];
        $methodArgs = collect($args)
            ->forget([0, 1])
            ->toArray();
    } else {
        $transformer = fn ($str) => fn ($item) => $str($item);
        $method = $args[0];
        $methodArgs = collect($args)
            ->forget(0)
            ->toArray();
    }

    $str = fn ($x) => (string) Str::of($x)->{$method}(...$methodArgs);

    return $this->transform($transformer($str));
});

/**
 * Filter the collection by testing the value at the array or dot-notated path
 * against the given named method of the `Str` helper class, and get the result
 * as a new collection. Prepend the method with `!` to test the inverse.
 */
Collection::macro('filterStr', function (...$args) {
    if ($this->isEmpty()) {
        return $this;
    }

    // use `@` as path to filter by key
    if ($args[0] === '@') {
        $compare = fn ($filter) => fn ($x, $y) => $filter($y);
        $method = $args[1];
        $methodArgs = collect($args)
            ->forget([0, 1])
            ->toArray();
    } else {
        if (['array' => true, 'object' => true][gettype($this->first())] ?? false) {
            // use first arg as key/path when items are arrays/collections
            $compare = fn ($filter) => fn ($x) => $filter(Hotdot::get($x, $args[0]));
            $method = $args[1];
            $methodArgs = collect($args)
                ->forget([0, 1])
                ->toArray();
        } else {
            // otherwise, use first arg as method
            $compare = fn ($filter) => fn ($x) => $filter($x);
            $method = $args[0];
            $methodArgs = collect($args)
                ->forget(0)
                ->toArray();
        }
    }

    // allow inversion via method prefix of `!`
    $test = str_split($method)[0] !== '!';
    $method = Str::remove('!', $method);

    // define filter function
    $filter = fn ($x) => (bool) Str::of($x)->{$method}(...$methodArgs) === $test;

    return $this->filter($compare($filter));
});

/**
 * Transform each element into an associative array with the given key mapped
 * onto the existing value.
 */
Collection::macro('applyKey', function ($key) {
    return $this->map(fn ($x) => [$key => $x]);
});

Collection::macro('applyKeys', function ($keys) {
    if (is_array($keys)) {
        $keys = collect($keys);
    }

    return $keys->combine($this)->toArray();
    // return $this->map(fn ($x) => );
});

/**
 * Copy the value of an existing array or collection property into another
 * property with the given key.
 */
Collection::macro('copyKeyAs', function ($source, $target) {
    return $this->map(
        fn ($x) => is_array($x) ? array_merge($x, [$target => $x[$source]]) :
            $x->put($target, $x->get($source))
    );
});

/**
 * Pull the value of the given key from the first collection item matching the
 * query.
 */
Collection::macro('pullFirstWhere', function ($pull, ...$args) {
    return $this->then()->firstWhere(...$args)->pull($pull);
});

/**
 * Perform the next-chained collection method on each collection item as if it
 * were a collection, and get the result as a new collection.
 */
Collection::macro('applyEach', function () {
    return new CollectionProxy($this, 'applyEach');
});

/**
 * Cast every item in the collection to an integer.
 */
Collection::macro('toInteger', function () {
    return $this->map(fn ($x) => (int) $x);
});

Collection::macro('groupByKeys', function () {
    return $this->mapToGroups(fn ($x, $y) => [$x => $y]);
});

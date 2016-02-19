<?php
namespace Glow\Support\Facades;

use Illuminate\Support\Facades\Schema as IlluminateSchema;
use Glow\Database\Schema\Blueprint;

class Schema extends IlluminateSchema
{

    /**
     * Get custom Blueprint resolver for schema builder instance
     *
     * @return \Closure
     */
    protected static function getBlueprintResolver() {
        return function ($table, $callback) {
            return new Blueprint($table, $callback);
        };
    }

    /**
     * Apply custom Blueprint resolver to schema builder instance
     *
     * @param \Illuminate\Database\Schema\Builder $builder
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected static function applyBlueprintResolver($builder) {
        $builder->blueprintResolver(static::getBlueprintResolver());

        return $builder;
    }

    /**
     * Get a schema builder instance for the default connection.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected static function getFacadeAccessor() {
        return static::applyBlueprintResolver(parent::getFacadeAccessor());
    }

    /**
     * Get a schema builder instance for a connection.
     *
     * @param  string $name
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    public static function connection($name) {
        return static::applyBlueprintResolver(parent::connection($name));
    }

}
<?php

namespace Glow;

trait Validatable
{

    public static function baseRules() {

        return [];
    }

    public static function rules() {

        return static::baseRules();
    }

    public function ownRules() {

        return static::rules();
    }
}
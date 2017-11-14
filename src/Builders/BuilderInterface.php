<?php

namespace App\Builders;

interface BuilderInterface
{
    /**
     * Create a builder with default state.
     * This default state reduce the amount of steps necessary when trying to get a final entity in an specified state
     */
    public static function default();

    /**
     * Given that we have the desired state, then create the real entity we want
     */
    public function build();
}

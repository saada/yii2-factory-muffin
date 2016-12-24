<?php

namespace saada\FactoryMuffin;

/**
 * This interface must be implemented by model in order to be loaded with
 * saada\FactoryMuffin\FactoryMuffin::loadModelDefinitions
 */
interface FactoryInterface
{
    /**
     * @return array used later by FactoryMuffin to generate random data
     */
    public static function definitions();
}

<?php
namespace saada\FactoryMuffin;

interface FactoryInterface {
    /**
     * @return array used later by FactoryMuffin to generate random data
     */
    public static function definitions();
}
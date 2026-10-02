<?php

interface IDiscount
{
    public function applyDiscount(float $total): float;
}

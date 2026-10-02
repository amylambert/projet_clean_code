<?php

class Vip implements IDiscount
{
    public function applyDiscount(float $total): float
    {
        if ($total < 100) {
            return $total * 0.95;
        } elseif ($total >= 100 && $total < 300) {
            return $total * 0.90;
        }

        return $total * 0.85;
    }
}

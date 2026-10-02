<?php
class ThreeDaysDiscount implements IDiscount
{
    public function applyDiscount(float $total): float
    {
        if ($total <= 0) {
            throw new RuntimeException('Total must be greater than zero');
        }
        
        return $total - 20;
    }
}

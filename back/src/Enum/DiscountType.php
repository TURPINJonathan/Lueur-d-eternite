<?php

declare(strict_types=1);

namespace App\Enum;

enum DiscountType: string
{
    case PERCENT = 'percent';
    case FIXED_AMOUNT = 'fixed_amount';
}

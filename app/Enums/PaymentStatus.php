<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case SETTLEMENT = 'settlement';
    case CANCEL = 'cancel';
    case EXPIRED = 'expired';
    case DENY = 'deny';
}
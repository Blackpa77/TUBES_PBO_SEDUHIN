<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case QRIS = 'qris';
    case CASH = 'cash';
    case SALDO = 'saldo';
}

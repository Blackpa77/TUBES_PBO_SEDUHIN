<?php

namespace App\Enums;

enum MenuCategory: string
{
    case COFFEE = 'coffee';
    case NON_COFFEE = 'noncoffee';
    case TEA = 'tea';
    case MILK = 'milk';
    case FRAPPE = 'frappe';
}

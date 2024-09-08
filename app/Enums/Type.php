<?php

namespace App\Enums;

enum Type: string
{
    case INCOME = 'INCOME';
    case SPENDING = 'SPENDING';
    case LOAN = 'LOAN';
}

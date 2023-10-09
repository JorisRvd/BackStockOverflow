<?php

namespace App\Enums;


enum OrderStatus: string {
    
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

}
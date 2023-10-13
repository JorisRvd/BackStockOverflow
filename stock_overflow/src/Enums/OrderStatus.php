<?php

namespace App\Enums;


enum OrderStatus: string {
    
    case En_attente = 'en attente';
    case Validé = 'validé';
    case Annulé = 'annulé';

}
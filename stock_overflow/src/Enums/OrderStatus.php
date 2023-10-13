<?php

namespace App\Enums;


enum OrderStatus: string {
    
    case En_attente = 'En attente';
    case Validé = 'validé';
    case Annulé = 'annulé';

}
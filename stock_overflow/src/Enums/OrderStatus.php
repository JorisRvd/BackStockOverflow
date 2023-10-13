<?php

namespace App\Enums;


enum OrderStatus: string {
    
    case En_attente = 'En attente';
    case Validé = 'Validée';
    case Annulé = 'Annulé';

}
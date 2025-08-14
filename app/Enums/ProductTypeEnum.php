<?php

namespace App\Enums;

enum ProductTypeEnum: string
{
    case DELIVERABLE = 'Entrega física';
    case DOWNLOADABLE = 'Download';
}
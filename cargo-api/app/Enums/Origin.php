<?php

namespace App\Enums;

enum Origin: string
{
    case Shanghai = 'CNSGH';
    case Singapore = 'SGSIN';
    case Shenzhen = 'CNSNZ';
    case Ningbo = 'CNNBO';
    case Guangzhou = 'CNGGZ';
}

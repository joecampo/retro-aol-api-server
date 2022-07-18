<?php

//@codingStandardsIgnoreStart

namespace App\Enums;

enum ChatPacket: string
{
    case CJ_PACKET = '5a95b30014182ba0434a0034000100010704000000020002000d';
    case cQ_PACKET = '5ac208001e131ba063510020000100010704000000040301{replace}0002000d';
    case Aa_PACKET = '5a81360023161ea04161012a0001000107040000001b010a04000001020301{replace}0002000d';
}
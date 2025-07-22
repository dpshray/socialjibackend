<?php

namespace App\Enums;

enum PaymentStatusEnum:string
{
    case TXN_INIT = 'txn_init';
    case AMOUNT_PAID = 'amount_paid';
    case DEPOSIT_ACCEPTED = 'deposit_accepted';
    case DELIVERED = 'delivered';
    case HANDOVERED = 'handovered';
    case AMOUNT_CLAIMED = 'amount_claimed';
    case COMPLAINED = 'complained';
}

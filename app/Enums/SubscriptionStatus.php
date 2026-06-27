<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case CANCELED = 'canceled';
    case PAST_DUE = 'past_due';
}
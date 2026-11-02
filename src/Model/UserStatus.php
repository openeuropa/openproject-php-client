<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * Activation statuses of a user account.
 */
enum UserStatus: string
{
    case ACTIVE = 'active';
    case REGISTERED = 'registered';
    case LOCKED = 'locked';
    case INVITED = 'invited';
}

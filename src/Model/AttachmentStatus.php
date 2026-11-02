<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * Statuses of an attachment's upload and virus scan.
 */
enum AttachmentStatus: string
{
    case UPLOADED = 'uploaded';
    case PREPARED = 'prepared';
    case SCANNED = 'scanned';
    case QUARANTINED = 'quarantined';
    case RESCAN = 'rescan';
}

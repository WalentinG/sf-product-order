<?php

declare(strict_types=1);

namespace Order;

enum OrderStatus: string
{
    case processing = 'Processing';
    case completed = 'Completed';
    case failed = 'Failed';
}

<?php

return [
    // Buyer pays 1.25% only when the assignment is completed. Seller pays 0.
    'buyer_commission_rate' => (float) env('AQAR_BUYER_COMMISSION_RATE', 0.0125),
    'seller_commission_rate' => 0.0,

    // Typical developer cancellation penalty used by the free exit calculator.
    'default_cancellation_percent' => (float) env('AQAR_CANCELLATION_PERCENT', 15),

    // Uploads (private documents are never exposed in the public API)
    'document_disk' => env('AQAR_DOCUMENT_DISK', 'local'),
    'image_disk' => env('FILESYSTEM_DISK', 'public'),
];

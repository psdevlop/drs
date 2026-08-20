<?php

namespace App\Services\Seo;

use RuntimeException;

/**
 * Thrown when SerpApi rate-limits a request (HTTP 429 or a rate-limit error
 * payload). Retried with exponential backoff by SerpApiClient.
 */
class RateLimitException extends RuntimeException {}

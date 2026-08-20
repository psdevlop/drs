<?php

return [
    // BangBang REST base URL (no trailing slash).
    'api_url' => env('CHAT_API_URL', 'http://187.127.207.132:8100'),

    // Browser WebSocket URL. In production behind TLS this should be wss://.
    'ws_url' => env('CHAT_WS_URL', 'ws://187.127.207.132:8100/ws'),

    // Service key used with X-Service-Key header on server-to-server calls.
    // MUST be set in .env; the widget stays disabled until this is present.
    'service_key' => env('CHAT_SERVICE_KEY'),

    // How long (seconds) to cache a minted access token before re-minting.
    // Actual token TTL comes from BangBang; we cap our cache to be safe.
    'token_cache_seconds' => env('CHAT_TOKEN_CACHE_SECONDS', 300),

    // HTTP timeout in seconds for server-to-server calls.
    'http_timeout' => env('CHAT_HTTP_TIMEOUT', 8),
];

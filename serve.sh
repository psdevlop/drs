#!/bin/bash
# Start Laravel dev server with increased upload limits for video files
php -d upload_max_filesize=100M -d post_max_size=105M -d max_execution_time=300 -d max_input_time=300 -d memory_limit=256M artisan serve

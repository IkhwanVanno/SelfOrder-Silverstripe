#!/bin/bash

# Update composer dependencies
if [ ! -d "vendor" ]; then
    composer install
    composer vendor-expose
    composer require dompdf/dompdf
fi

# Flush Silverstripe cache & dev/build
vendor/bin/sake dev/build flush=all

# Start Apache in foreground
apache2-foreground

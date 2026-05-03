#!/bin/bash

# Lancer les migrations automatiquement
php artisan migrate --force

# Lancer Apache
apache2-foreground

#!/bin/zsh
# Deploy Laravel backend to cPanel: repo at REMOTE_ROOT, web root REMOTE_PUBLIC (SSH).
set -euo pipefail

SERVER="mvshoots@mvshoots.com"
PORT="19199"
REMOTE_ROOT="/home5/mvshoots/mvshoots_repo"
REMOTE_PUBLIC="/home5/mvshoots/public_html"

echo "Pushing backend to origin/main..."
git push origin main

echo "Deploying on cPanel host via SSH (${SERVER}:${PORT})..."
ssh -p "$PORT" "$SERVER" "
  set -e
  cd '$REMOTE_ROOT'
  git fetch origin main
  git reset --hard origin/main
  rm -f public/hot '$REMOTE_PUBLIC/hot'
  if command -v composer >/dev/null 2>&1; then
    composer install --no-dev --no-interaction --optimize-autoloader
  fi
  npm run build
  php artisan migrate --force
  php artisan optimize:clear
  php artisan optimize
  rsync -a --delete public/build/ '$REMOTE_PUBLIC/build/'
  rsync -a public/.htaccess public/.user.ini public/favicon.ico public/robots.txt '$REMOTE_PUBLIC/'
  cat > '$REMOTE_PUBLIC/index.php' <<'PHP'
<?php

use Illuminate\\Foundation\\Application;
use Illuminate\\Http\\Request;

define('LARAVEL_START', microtime(true));

if (file_exists(\$maintenance = '$REMOTE_ROOT/storage/framework/maintenance.php')) {
    require \$maintenance;
}

require '$REMOTE_ROOT/vendor/autoload.php';

/** @var Application \$app */
\$app = require_once '$REMOTE_ROOT/bootstrap/app.php';

\$app->handleRequest(Request::capture());
PHP
"

echo "Deployment complete."

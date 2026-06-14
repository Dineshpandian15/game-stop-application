#!/usr/bin/env bash
# Package Game Stop for Windows shop PCs (PHP + browser app mode — no Electron).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
VERSION="${1:-1.0.0}"
OUTPUT_NAME="GameStop-Windows-Shop-${VERSION}"
BUILD_DIR="$(mktemp -d)"
STAGING="${BUILD_DIR}/${OUTPUT_NAME}"
PHP_SOURCE="${ROOT}/vendor/nativephp/php-bin/bin/win/x64/php-8.4.zip"
PHP_SOURCE_FALLBACK="${ROOT}/vendor/nativephp/php-bin/bin/win/x64/php-8.5.zip"
ZIP_PATH="${HOME}/Downloads/${OUTPUT_NAME}.zip"

echo "==> Packaging Game Stop for Windows (${VERSION})"
echo "    Staging: ${STAGING}"

mkdir -p "${STAGING}"

echo "==> Copying application files..."
rsync -a \
  --exclude '.git' \
  --exclude '.env' \
  --exclude 'node_modules' \
  --exclude 'nativephp' \
  --exclude 'tests' \
  --exclude '.phpunit.cache' \
  --exclude '.phpunit.result.cache' \
  --exclude ':memory:' \
  --exclude 'storage/logs/*' \
  --exclude 'storage/framework/cache/data/*' \
  --exclude 'storage/framework/sessions/*' \
  --exclude 'storage/framework/views/*' \
  --exclude 'database/*.sqlite' \
  --exclude 'database/*.sqlite-*' \
  "${ROOT}/" "${STAGING}/"

echo "==> Installing PHP dependencies (production)..."
(cd "${STAGING}" && composer install --no-dev --optimize-autoloader --no-interaction --quiet)

echo "==> Removing NativePHP from shop package (browser mode only)..."
(cd "${STAGING}" && composer remove nativephp/desktop --no-interaction --quiet 2>/dev/null || true)
rm -f "${STAGING}/config/nativephp.php"
rm -rf "${STAGING}/app/Providers/NativeAppServiceProvider.php"

echo "==> Clearing cached config..."
(cd "${STAGING}" && php artisan config:clear --quiet 2>/dev/null || true)

echo "==> Building frontend assets..."
if [[ -f "${STAGING}/package.json" ]]; then
  (cd "${STAGING}" && npm ci --ignore-scripts --silent 2>/dev/null || npm install --ignore-scripts --silent)
  (cd "${STAGING}" && npm run build --silent)
  rm -rf "${STAGING}/node_modules"
fi

echo "==> Copying Windows launcher..."
mkdir -p "${STAGING}/windows-launcher"
cp "${ROOT}/windows-launcher/"* "${STAGING}/windows-launcher/"

echo "==> Bundling Windows PHP runtime (8.4+ required for Laravel 13)..."
mkdir -p "${STAGING}/runtime"
if [[ -f "${PHP_SOURCE}" ]]; then
  unzip -p "${PHP_SOURCE}" php.exe > "${STAGING}/runtime/php.exe"
elif [[ -f "${PHP_SOURCE_FALLBACK}" ]]; then
  unzip -p "${PHP_SOURCE_FALLBACK}" php.exe > "${STAGING}/runtime/php.exe"
else
  echo "ERROR: Windows PHP 8.4+ not found in vendor/nativephp/php-bin."
  echo "Run: composer install"
  exit 1
fi
chmod +x "${STAGING}/runtime/php.exe"

echo "==> Preparing .env.windows..."
APP_KEY="$(grep '^APP_KEY=' "${ROOT}/.env" 2>/dev/null | cut -d= -f2- || true)"
cat > "${STAGING}/.env.windows" <<EOF
APP_NAME="Game Stop"
APP_ENV=production
APP_KEY=${APP_KEY:-base64:CHANGE_ME_RUN_php_artisan_key_generate}
APP_DEBUG=false
APP_URL=http://127.0.0.1:8765

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning

DB_CONNECTION=sqlite
DB_DATABASE=database/game_stop.sqlite

SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=file

VITE_APP_NAME="Game Stop"
EOF

mkdir -p "${STAGING}/database" "${STAGING}/storage/logs"
touch "${STAGING}/database/.gitkeep"

echo "==> Creating zip..."
rm -f "${ZIP_PATH}"
(cd "${BUILD_DIR}" && zip -r -q "${ZIP_PATH}" "${OUTPUT_NAME}")

rm -rf "${BUILD_DIR}"

SIZE="$(du -h "${ZIP_PATH}" | cut -f1)"
echo ""
echo "Done! Windows shop package ready:"
echo "  ${ZIP_PATH}  (${SIZE})"
echo ""
echo "On Windows:"
echo "  1. Extract the zip (e.g. C:\\GameStop)"
echo "  2. Double-click windows-launcher\\Start Game Stop.vbs"

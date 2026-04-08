#!/usr/bin/env zsh
# Build Laravel Vite assets and release APKs for user_app, partner_app, owner_app.
# Output: ../Deliverables/<app>-<timestamp>.apk (repo-root Deliverables/; gitignored)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
STAMP="$(date +%Y%m%d-%H%M)"
OUT="${ROOT}/Deliverables"
mkdir -p "$OUT"

echo "== Laravel: npm run build =="
cd "$ROOT"
npm run build

echo "== Flutter APKs =="
for APP in user_app partner_app owner_app; do
  echo "---- $APP ----"
  cd "${ROOT}/apps/${APP}"
  flutter clean
  flutter pub get
  flutter build apk --release
  cp build/app/outputs/flutter-apk/app-release.apk "${OUT}/${APP}-${STAMP}.apk"
  echo "OK ${OUT}/${APP}-${STAMP}.apk"
done

ls -la "$OUT"/*.apk 2>/dev/null || true
echo "Done."

#!/usr/bin/env bash
set -euo pipefail

# Build a PrestaShop-ready ZIP with top-level folder `buhexport/`

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DIST_DIR="$ROOT_DIR/dist"
PKG_DIR="$ROOT_DIR/.package"

rm -rf "$PKG_DIR" && mkdir -p "$PKG_DIR/buhexport" "$DIST_DIR"

# Copy module files into package folder
rsync -a --delete \
  --exclude '/.git/' \
  --exclude '/.package/' \
  --exclude '/dist/' \
  --exclude '/scripts/' \
  --exclude '*.zip' \
  "$ROOT_DIR/" "$PKG_DIR/buhexport/"

cd "$PKG_DIR"
rm -f "$DIST_DIR/buhexport.zip"
zip -r "$DIST_DIR/buhexport.zip" buhexport

echo "Built: $DIST_DIR/buhexport.zip"

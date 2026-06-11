#!/usr/bin/env bash
# ═══════════════════════════════════════════════════════════════════
#  InteTeam Support — One-shot Ubuntu server deployment script
#
#  ╔═══════════════════════════════════════════════════════════════╗
#  ║  WARNING: Runs docker compose up -d --build.                 ║
#  ║  FIRST-TIME deployment only. For updates use post-deploy.sh. ║
#  ║  Never re-run on a live server — use Panel deploy instead.   ║
#  ╚═══════════════════════════════════════════════════════════════╝
#
#  Usage:
#    sudo bash deploy.sh --domain=support.inte.team --ssl=npm --port=8094
#
#  This script is a thin wrapper around install.sh.
#  It is kept separate so the Panel can reference deploy.sh for
#  first-time deploys and post-deploy.sh for all subsequent updates.
# ═══════════════════════════════════════════════════════════════════
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
exec bash "$SCRIPT_DIR/install.sh" "$@"

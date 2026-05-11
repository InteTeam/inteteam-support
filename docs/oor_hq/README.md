# OOR-HQ — User Manual Scope

## Overview

OOR-HQ is the control plane for the Vintage Internet Project (VIP). It lets families browse the modern web from vintage hardware (Windows 95/98, IE6, Netscape) by streaming a rendered browser session from the server. Also provides a game store (OOR-Store) for retro community games.

**Status:** Feature complete (Phase 1). Deployment pending.

---

## Roles

| Role | Access |
|------|--------|
| **Family Member** | VIP dashboard, enter VIP sessions, browse OOR-Store, view high scores |
| **Company Admin** | Manage VIP members (enable/disable access), view usage |
| **Root Admin** | Provision/deprovision LXC containers, manage all companies' VIP accounts |

---

## Guides to Write

### VIP Sessions
- [01 - VIP Overview](01-vip-overview.md) — What VIP is, how it works
- [02 - Entering a VIP Session](02-entering-session.md) — Click "Enter VIP", JWT auth, reconnect
- [03 - Session Lifecycle](03-session-lifecycle.md) — Heartbeat, idle warning, auto-stop, grace period
- [04 - Usage Tracking](04-usage-tracking.md) — Monthly minutes, final hour warning

### VIP Administration
- [10 - Managing VIP Members](10-managing-members.md) — Enable/disable access per family member
- [11 - VIP Settings](11-vip-settings.md) — Company VIP configuration

### Root Admin
- [20 - Provisioning VIP Accounts](20-provisioning.md) — Create/destroy LXC containers
- [21 - Managing LXC Containers](21-lxc-management.md) — Start, stop, rebuild, delete

### OOR-Store (Phase 2)
- [30 - Browsing the Store](30-oor-store.md) — View games, screenshots, descriptions
- [31 - Deploying Games](31-deploy-games.md) — "Deploy to my VIP" button
- [32 - Uploading Games](32-upload-games.md) — Upload JS/HTML games, auto-scanner
- [33 - High Scores](33-high-scores.md) — Company scores, global leaderboard

### Project Sharing
- [40 - Project Sharing Overview](40-project-sharing-overview.md) — What it is, roles, relationship to board invites
- [41 - Sending an Email Invite](41-sending-email-invite.md) — Invite a specific person by email
- [42 - Generating a Team Link](42-generating-team-link.md) — Multi-use shareable link
- [43 - Accepting a Project Invite](43-accepting-project-invite.md) — How invitees join a project
- [44 - Viewing Shared Projects](44-shared-projects.md) — "Shared with me" tab on Projects index
- [45 - Managing Project Members](45-managing-project-members.md) — Revoke invites, remove members, change roles

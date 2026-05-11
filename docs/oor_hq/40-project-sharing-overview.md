# Project Sharing Overview

## What is Project Sharing?

Project sharing lets you invite people from outside your company to collaborate on a specific project. When you share a project, the invited person gets access to everything inside it — Kanban boards, Mindmaps, and Notes — without needing an account in your company.

This is the quickest way to bring in an external collaborator (e.g. a developer from a partner company) without managing separate board-by-board invites.

---

## How it Works

1. Open a project and click the **Share** button
2. Choose how to invite: by email (one person) or via a shareable team link (multiple people)
3. Select a role for the invitee (Viewer, Collaborator, or Admin)
4. The invitee receives an invite, logs in, and immediately gains access

---

## Roles

| Role | What they can do |
|------|-----------------|
| **Viewer** | Read-only access to all boards, mindmaps, and notes. Cannot create or edit anything. |
| **Collaborator** | Create and edit Kanban cards, add and edit mindmap nodes, add notes. Cannot manage project members or delete columns/boards. |
| **Admin** | Full access — create/delete boards and columns, manage mindmaps, invite and remove project members. |

---

## What Happens When Access is Removed

Revoking someone's project access is a single action that removes everything at once:

- Their `ProjectMember` record is deleted
- All their board-level memberships inside that project are also deleted

There is no partial removal — it's all or nothing at the project level.

---

## Relationship to Board-Level Invites

Project sharing and board-level invites coexist. If someone already has a board invite with role `collaborator` and then receives a project invite with role `admin`, their effective role becomes `admin` (the higher role wins). Revoking the project share removes their access entirely, including any existing board-level memberships within that project.

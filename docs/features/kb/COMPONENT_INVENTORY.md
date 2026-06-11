# Knowledge Base Surface — Component Inventory

## PHP

| File | Type | Purpose |
|------|------|---------|
| `app/Services/KbService.php` | Service | Queries inteteam-rag, increments kb_lookups counter |
| `app/Http/Controllers/Customer/KbController.php` | Controller | AJAX suggest endpoint for customer routes |
| `app/Http/Controllers/Tenant/KbController.php` | Controller | AJAX suggest endpoint for tenant routes |

## React

| File | Purpose |
|------|---------|
| `resources/js/components/KbSuggestions.tsx` | Shared panel: "Did you check these articles?" |
| `resources/js/Pages/Customer/Tickets/Create.tsx` | Updated: debounced suggest on description change |
| `resources/js/Pages/Tenant/Tickets/Create.tsx` | Updated: debounced suggest on description change |
| `resources/js/Pages/Customer/Chat/Widget.tsx` | Updated: KB search bar on chat start page |
| `resources/js/Pages/Tenant/Groups/Index.tsx` | Updated: `kb` feature toggle added |

## Routes

| Method | Path | Handler |
|--------|------|---------|
| POST | `/support/kb/suggest` | `Customer\KbController::suggest` |
| POST | `/portal/kb/suggest` | `Tenant\KbController::suggest` |

## Config / env

| Key | Purpose |
|-----|---------|
| `RAG_URL` | Base URL of inteteam-rag service |

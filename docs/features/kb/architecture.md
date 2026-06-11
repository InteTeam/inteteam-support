# Knowledge Base Surface — Architecture

## Data model

No new tables. Uses the existing `usage_counters` table:

```
metric: kb_lookups
period: YYYY-MM
count:  increments on every successful RAG query
```

## Service layer

`KbService`
- `suggest(Tenant $tenant, string $query, int $limit = 3): array`
  - Returns `[]` if `$query` is blank (< 10 chars after trim)
  - POSTs to `{RAG_URL}/api/search` with 3s timeout
  - On success: increments `kb_lookups` counter, returns articles array
  - On HTTP error / timeout / inteteam-rag down: returns `[]`, logs warning — never throws

## Feature gate check location

`KbController::suggest()` — before calling `KbService::suggest()`:
```php
$group = $request->user()->customerGroup;
if ($group && ! ($group->features['kb'] ?? false)) {
    return response()->json(['articles' => []]);
}
```

This means no RAG call is made and no `kb_lookups` counter fires when the group has `kb` disabled.

## AJAX endpoints (return JSON, used by React)

| Route | Controller | Who calls it |
|-------|-----------|--------------|
| `POST /support/kb/suggest` | `Customer\KbController::suggest` | Customer ticket create + chat start |
| `POST /portal/kb/suggest` | `Tenant\KbController::suggest` | Tenant portal ticket create |

## React integration

### Shared component: `resources/js/components/KbSuggestions.tsx`

Props: `{ articles: KbArticle[]; loading: boolean }`
Renders a soft "Did you check these articles?" panel with article links. Hidden when `articles` is empty and not loading.

### Ticket create pages (Customer + Tenant)

- `useRef` debounce timer
- `onChange` on description textarea → after 600 ms of no typing → `POST .../kb/suggest` with `{ query: description }`
- Show `KbSuggestions` below the textarea
- Counter fires only on the HTTP call, not on every keystroke

### Chat widget (`Customer/Chat/Widget.tsx`)

- When `!activeSession` (start state): show a search bar pre-populated with nothing
- On description input (debounced 600 ms) → `POST /support/kb/suggest`
- Show suggestions above "Start Chat" button
- Suggestions disappear once chat starts

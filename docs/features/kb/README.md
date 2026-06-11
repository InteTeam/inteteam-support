# Knowledge Base Surface — Feature Documentation

## User stories

| As a… | I want to… | So that… |
|---|---|---|
| End customer | See relevant help articles before submitting a ticket | I can self-serve and avoid unnecessary tickets |
| End customer | See relevant articles before starting a live chat | I can resolve my issue immediately without waiting for an engineer |
| Tenant admin | Toggle KB suggestions on/off per customer group | Premium groups get guided self-service; basic groups don't |

## Acceptance criteria

- [ ] Customer ticket creation form queries inteteam-rag on description change (debounced 600 ms, min 10 chars)
- [ ] Up to 3 article suggestions shown inline below the description field ("Did this help?")
- [ ] Tenant portal ticket creation form has the same behaviour
- [ ] Chat start page shows KB suggestions before the "Start Chat" button
- [ ] `kb_lookups` counter increments on every RAG query (per tenant, per billing period)
- [ ] KB suggestions are skipped (no RAG call, no counter) when customer's group has `kb: false`
- [ ] inteteam-rag unavailability is silent — form works normally, no suggestions shown

## Feature flag

`CustomerGroup.features['kb']` — boolean. Defaults to `false` (opt-in).
Toggled in the Tenant Groups page alongside `chat` and `remote`.

## inteteam-rag API contract

`POST {RAG_URL}/api/search`

Request:
```json
{ "query": "my printer is not feeding paper", "limit": 3 }
```

Response:
```json
{
  "articles": [
    { "title": "Printer paper feed troubleshooting", "url": "https://help.inte.team/...", "excerpt": "If your printer…" },
    ...
  ]
}
```

On HTTP error or timeout (3 s): return empty array, log warning.

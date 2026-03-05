# API Endpoints

All mutating endpoints require:

- Authenticated session
- CSRF token (`_csrf` form field or `X-CSRF-Token` header)

## Auth

- `POST /auth/login`
- `POST /auth/logout`

## Dashboard

- `GET /dashboard/{role}`

## Sessions and Submission

- `GET /sessions/{id}`
- `POST /sessions/{id}/submissions`
- `PATCH /submissions/{id}/items/{itemId}`

## Signed Sheet

- `POST /submissions/{id}/signed-sheet`
- `GET /submissions/{id}/signed-sheet`
- `GET /submissions/{id}/signed-sheet/history`

## Decisions

- `POST /submissions/{id}/approve`
- `POST /submissions/{id}/reject`
- `POST /submissions/{id}/override`

## Reports

- `GET /reports/attendance`
- `GET /reports/approval-turnaround`
- `GET /reports/escalations`

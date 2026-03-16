# Distribution Changes — Implementation Plan

---

## Change 0 — Status Per Estimator (Schema Change — Must Do First)

### Overview
Currently `status` (open/submitted) lives on the `allocations` table — one status per job. It needs to move to the `allocation_user` pivot table so each estimator has their own status per job.

---

### 0.1 — Schema change

**Add `status` to `allocation_user` pivot:**

```
allocation_user.status  enum('open','submitted')  default 'open'
```

**Remove `status` from `allocations` table** (or keep as deprecated — safe to drop since it's replaced).

---

### 0.2 — Impact on existing code

Every place that reads or writes `allocation.status` must be updated to use the pivot:

| Location | Change |
|---|---|
| `AllocationController::store()` — attach estimators | Pass `['status' => 'open']` on `attach()` |
| `Estimator/AllocationController::updateStatus()` | Update pivot row: `$allocation->estimators()->updateExistingPivot($userId, ['status' => $newStatus])` |
| `AllocationController` load calculation | Change `->where('allocations.status', 'open')` → `->where('allocation_user.status', 'open')` |
| `Allocation` model | Add `withPivot('status')` to `estimators()` relationship |
| `SendJobReminders` command | Filter by pivot status instead of allocation status |
| Admin allocation index table | Show per-estimator status (see 0.3) |
| Estimator workload index | Read status from `$allocation->pivot->status` |
| Estimator dashboard | Same |
| Monthly view load totals | Update load query to use pivot status |

---

### 0.3 — Display in admin allocation table

Each estimator chip in the Assigned Estimators column shows their letter + status:

```
A — Open    B — Submitted    C — Open
```

Color-coded badges:
- Open → yellow
- Submitted → green

---

### 0.4 — Estimator limit enforcement

- MU jobs: max **3** estimators at all times
- NON_MU jobs: max **2** estimators at all times

Enforced in:
- `AllocationController::store()` — already done via `$assignCount`
- `AllocationController::update()` (edit) — validate that new total does not exceed limit before saving

---

### 0.5 — Files to change

- `database/migrations/` — add `status` to `allocation_user`, drop `status` from `allocations`
- `app/Models/Allocation.php` — add `withPivot('status')` to `estimators()`, remove `status` from `$fillable`
- `app/Http/Controllers/Admin/AllocationController.php` — update attach + load query
- `app/Http/Controllers/Estimator/AllocationController.php` — update `updateStatus()` to use pivot
- `app/Console/Commands/SendJobReminders.php` — update status filter
- All views that reference `$allocation->status`

---

---

## Change 1 — Auto-create Projects on Distribution Assignment

### Overview
When a distribution job is created and estimators are assigned, the system automatically creates one Project entry per estimator and links it back to the allocation.

---

### 1.1 — New fields on the Allocation form (NOT stored in allocations table)

No new columns added to `allocations`. These are form-only inputs used exclusively to populate the auto-created Project entries.

An **"Other Information for Project"** collapsible section is added at the bottom of the allocation form containing:

| Form field | Maps to Project field | Input type | Notes |
|---|---|---|---|
| Project Name | `name` | text | Required. Final name = `{job_number}{letter}. {input}` |
| GC | `gc` | `<select>` | Populated from `gcs` where `is_active = true`, ordered by name. Stores GC name string. |
| Status | `status` | `<select>` | Populated from `statuses` via `Status::ordered()->get()`, shows name + color dot. Stores status name string. |
| Project Information | `project_information` | textarea | Free text, nullable |

> These fields are passed as transient inputs in the request — they are never persisted on the `allocations` table.

---

### 1.2 — Auto-generated Project name convention

When projects are created, the name is built as:

```
{job_number}{letter}. {project_name}
```

Letters are assigned in the order estimators are selected (sorted by effective load, location-filtered):
- 1st estimator → A
- 2nd estimator → B
- 3rd estimator → C

Examples: `5454A. Downtown Tower`, `5454B. Downtown Tower`, `5454C. Downtown Tower`

> `project_name` is required. `job_number` and the letter prefix are always prepended automatically.

---

### 1.3 — Link: `allocation_id` on Projects

Add `allocation_id` (nullable, FK to `allocations`) to the `projects` table.

- When a project is auto-created from a distribution, `allocation_id` is set.
- Manually created projects keep `allocation_id = null`.
- Add inverse relationship: `Allocation::hasMany(Project)` and `Project::belongsTo(Allocation)`.

---

### 1.4 — Project fields populated at creation

| Project field | Value |
|---|---|
| `name` | `{job_number}{letter}. {project_name}` |
| `gc` | from allocation form input |
| `assigned_to` | estimator's user id |
| `due_date` | `allocation.assigned_date` (estimator's due date) |
| `assigned_date` | today (date distribution is created) |
| `type` | `MU` → `"MULTIUNIT"`, `NON_MU` → `"NON MU"` |
| `status` | from allocation form input (Status select) |
| `project_information` | from allocation form input |
| `allocation_id` | allocation id |

---

### 1.5 — Link in Admin Allocation Index

In the allocation index table, each estimator chip becomes a clickable link → `admin.projects.show` for their auto-created project.

If the project was deleted manually, show the estimator name as plain text (no broken link).

---

### 1.6 — Files to change

- `database/migrations/` — add `allocation_id` to `projects` table only (no changes to `allocations`)
- `app/Models/Allocation.php` — add `projects()` hasMany
- `app/Models/Project.php` — add `allocation_id` to fillable, add `allocation()` belongsTo
- `app/Http/Controllers/Admin/AllocationController.php`
  - `store()`: validate transient project fields, loop through `$selected` and create one Project per estimator
  - `index()`: pass `$gcs` and `$statuses` to view
- `resources/views/admin/allocation/index.blade.php`
  - Add collapsible "Other Information for Project" section
  - Estimator chips → links to project page with per-estimator status badges

---

---

## Change 2 — Edit Assigned Estimators on a Distribution Job

### Overview
Admin can change who is assigned to an existing allocation. The system handles project updates and emails accordingly.

---

### 2.1 — Estimator slot limits (enforced on edit)

| Job type | Max estimators |
|---|---|
| MU | 3 |
| NON_MU | 2 |

The edit form must prevent adding beyond the limit. The current count is always shown.

---

### 2.2 — Edit flow

A new "Edit Estimators" button on each allocation row opens a dedicated edit page showing:
- Each current slot: letter, estimator name, status, linked project
- Swap control (replace estimator in that slot)
- Remove button per slot
- Add button (only shown if current count < limit)

On save, the system diffs old vs new:

| Case | Action |
|---|---|
| Estimator **swapped** (same slot, different person) | Update `project.assigned_to` to new estimator. Project name/letter unchanged. Send removal email to old, assignment email to new. |
| Estimator **removed**, no replacement | If project has no remarks/proposals/progress → delete project. If it has activity → keep project, set `assigned_to = null`, show warning flag in admin view. Send removal email. |
| Estimator **added** to new slot | Create new project with next available letter (never reuse). Send assignment email. |
| Estimator **unchanged** | No action. |

---

### 2.3 — Project letter on re-assignment

- Letters are determined by the order slots were created, never reused.
- Example: A, B, C exist. B is removed. A new estimator added → gets D (not B).
- Swap (B swapped for someone else) → keeps letter B, only `assigned_to` changes.

---

### 2.4 — Removal notification email

`JobRemovedMail` sent to removed estimator:
- Subject: `Job {job_number} — Assignment Removed`
- Body: informs them they are no longer assigned.

---

### 2.5 — Files to change

- `app/Http/Controllers/Admin/AllocationController.php` — `edit()` + `update()` methods
- `app/Mail/JobRemovedMail.php` + `resources/views/mail/job-removed.blade.php`
- `resources/views/admin/allocation/index.blade.php` — add Edit button per row
- `resources/views/admin/allocation/edit.blade.php` — edit estimators page
- `routes/web.php` — `GET /admin/allocation/{allocation}/edit` and `PUT /admin/allocation/{allocation}`

---

---

## Change 3 — Search & Filter in Distribution Index

### Filters to add

| Filter | Type |
|---|---|
| Job Number | text search |
| Estimator | dropdown (all estimators) |
| Job Type | dropdown (MU / NON_MU / All) |
| Due Date range | date from / date to |

> Status filter removed from here — status is now per estimator on the pivot, not per job.

### Files to change

- `app/Http/Controllers/Admin/AllocationController.php` — `index()`: apply filters to query
- `resources/views/admin/allocation/index.blade.php` — add filter bar above the table

---

## Execution Order

1. **Change 0** (pivot status) — must be done first, everything else depends on it
2. **Change 3** (search/filter) — isolated, no dependencies, quick win
3. **Change 1** (auto-create projects) — core feature, requires migration
4. **Change 2** (edit estimators) — depends on Change 1 (project letter logic)

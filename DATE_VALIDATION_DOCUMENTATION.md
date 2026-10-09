# Crystal Shipping Inc. - Seafarer Application Form
## Date Constraint & Future Date Restriction Implementation Documentation

**Module**: Date Picker & Validation Engine (`Crystal_App_Form/index.php`, `questionnaire/Crystal_AppForm.php`, `ApplicationFormPage.tsx`)  
**Organization**: Crystal Shipping Inc.  
**Date**: October 9, 2026  
**Status**: Verified & Implemented  

---

## 1. WHAT DID WE BUILD?

We implemented comprehensive, multi-layer future date restrictions and cross-field date interval validations across the **Seafarer Application Portal** (`Crystal_App_Form`) and the accompanying application script (`questionnaire/Crystal_AppForm.php`).

Specifically:
1. **HTML5 Native Picker Restrictions (`max` attributes)**:
   - Added `max="<?php echo $todayDate; ?>"` to `date_from[]` and `date_to[]` in static and dynamically added Shipboard Experience cards.
   - Removed erroneous `min="<?php echo $tomorrowDate; ?>"` that previously forced future-only dates.
   - Synchronized all Document "Issue Date" inputs (`passport_issue`, `sirb_issue`, `goc_issue`, `coc_issue`, `training_issue[]`, and legacy questionnaire fields) with `$todayDate` (`<?= date('Y-m-d') ?>`).
2. **Client-Side Cross-Field Date Constraint Synchronization (`syncExperienceDateConstraints`)**:
   - Dynamic real-time bidirectional constraints:
     - When `DATE FROM` is selected, the corresponding `DATE TO` input has its `min` attribute set to `DATE FROM`'s value, preventing the picker from permitting sign-off dates earlier than sign-on dates.
     - When `DATE TO` is selected, `DATE FROM` has its `max` attribute set to `DATE TO` (or today), preventing sign-on dates from extending beyond sign-off dates.
     - HTML5 custom validity messages (`setCustomValidity`) are automatically set and cleared on `change` and `input` events.
3. **Step Navigation & Row Insertion Validation (`validateExperiences`)**:
   - `validateExperiences()` strictly checks that neither `DATE FROM` nor `DATE TO` can be in the future (`> TODAY_DATE`), and that `DATE TO` is not earlier than `DATE FROM`.
   - `addExperienceRow()` now executes the same date checks on the active row before permitting candidates to append a subsequent sea service experience card.
   - Descriptive feedback messages are rendered inside `#experienceError` instead of generic prompt text.
4. **Backend POST Defensive Validation**:
   - In PHP (`Crystal_App_Form/index.php`), sea service sign-on (`$fr`) and sign-off (`$to`) dates are validated against `$dateApplied` (`date('Y-m-d')`). Any attempt to submit future dates is clamped at the server level.
   - Reversible duration calculation logic prevents negative intervals when calculating sea service duration.

---

## 2. WHY DID WE BUILD IT?

### Problem Statement
In maritime recruitment, **Shipboard Experience** records an applicant's historical sea service contracts (vessels, gross tonnage, rank onboard, sign-on date, and sign-off date). Sea service history describes completed service in the past; therefore:
1. Neither sign-on (`DATE FROM`) nor sign-off (`DATE TO`) can ever occur in the future (tomorrow or beyond).
2. A seafarer cannot sign off a vessel (`DATE TO`) before signing on (`DATE FROM`).

However, during form testing, applicants could open the native date picker calendar on `DATE FROM` and `DATE TO` and navigate to future dates (e.g., December 2026, when today is October 2026), selecting future contract dates. 

An exploratory attempt had inadvertently applied `min="<?php echo $tomorrowDate; ?>"` to `DATE TO`. In HTML5 date specifications:
- `min="tomorrow"` permits **only** future dates (tomorrow and later) and disables all past/present dates.
- `max="today"` permits **only** past/present dates (up to today) and disables all future dates.

This created an inverted constraint where past sea service could not be chosen, and future dates were enabled.

---

## 3. HOW DOES IT WORK?

### Technical Implementation

#### A. Global Date Initialization & Scoping
In PHP header (`Crystal_App_Form/index.php`):
```php
$todayDate = date('Y-m-d'); // Current date in Asia/Manila (UTC+8)
```
At the top of the primary `<script>` block:
```javascript
const TODAY_DATE = '<?php echo $todayDate; ?>';
```
Scoped at the root of the JavaScript execution context so both pre-existing static functions and dynamic row generation access the identical server-synchronized timestamp.

#### B. HTML5 Static Experience Card Inputs
```html
<div>
    <label class="block font-bold text-slate-700 mb-1">DATE FROM</label>
    <input type="date" name="date_from[]" max="<?php echo $todayDate; ?>" 
           onchange="syncExperienceDateConstraints(this)" 
           oninput="syncExperienceDateConstraints(this)" 
           class="w-full p-2.5 rounded-xl border border-slate-300">
</div>
<div>
    <label class="block font-bold text-slate-700 mb-1">DATE TO</label>
    <input type="date" name="date_to[]" max="<?php echo $todayDate; ?>" 
           onchange="syncExperienceDateConstraints(this)" 
           oninput="syncExperienceDateConstraints(this)" 
           class="w-full p-2.5 rounded-xl border border-slate-300">
</div>
```

#### C. Bidirectional Constraint Synchronization
```javascript
function syncExperienceDateConstraints(changedInput) {
    const card = changedInput ? (changedInput.closest('.grid') || changedInput.closest('.space-y-4')) : null;
    if (!card) return;
    const dateFrom = card.querySelector('input[name="date_from[]"]');
    const dateTo = card.querySelector('input[name="date_to[]"]');
    if (!dateFrom || !dateTo) return;

    const todayStr = TODAY_DATE || new Date().toISOString().split('T')[0];

    // Cap Date From at Date To (if Date To is earlier than today), otherwise cap at today
    const maxForFrom = (dateTo.value && dateTo.value < todayStr) ? dateTo.value : todayStr;
    dateFrom.setAttribute('max', maxForFrom);
    dateTo.setAttribute('max', todayStr);

    // Date To cannot start before Date From
    if (dateFrom.value) {
        dateTo.setAttribute('min', dateFrom.value);
    } else {
        dateTo.removeAttribute('min');
    }

    // HTML5 custom validity messaging
    if (dateFrom.value && dateFrom.value > todayStr) {
        dateFrom.setCustomValidity('Future dates are not allowed. Date From cannot be in the future.');
    } else {
        dateFrom.setCustomValidity('');
    }

    if (dateTo.value && dateTo.value > todayStr) {
        dateTo.setCustomValidity('Future dates are not allowed. Date To cannot be in the future.');
    } else if (dateFrom.value && dateTo.value && dateTo.value < dateFrom.value) {
        dateTo.setCustomValidity('Date To cannot be earlier than Date From.');
    } else {
        dateTo.setCustomValidity('');
    }
}
```

#### D. Dynamic Card Generation (`addExperienceRow`)
When candidates click `+ ADD EXPERIENCE`, the generated row template includes `max="${TODAY_DATE}"` and bidirectional handlers. Immediately upon DOM insertion, event listeners are attached:
```javascript
div.querySelectorAll('input[name="date_from[]"], input[name="date_to[]"]').forEach(input => {
    input.setAttribute('max', TODAY_DATE);
    input.addEventListener('change', function() { syncExperienceDateConstraints(this); });
    input.addEventListener('input', function() { syncExperienceDateConstraints(this); });
});
```

#### E. Navigation Validation Gate (`validateExperiences`)
Before moving to Step 5 (Review Application), `validateExperiences()` evaluates:
1. Every input in every experience row is populated.
2. `date_from.value <= TODAY_DATE`.
3. `date_to.value <= TODAY_DATE`.
4. `date_to.value >= date_from.value`.

If any condition fails, the focused field triggers `#experienceError` with a contextual explanation.

---

## 4. WHAT ALTERNATIVES EXISTED & WHY WERE THEY NOT CHOSEN?

| Alternative | Assessment | Reason for Rejection |
|---|---|---|
| **A. Pure JavaScript Post-Selection Alert (`alert()`)** | Popup alerts on change. | Degrades user experience; annoying modal interrupts; does not prevent the datepicker calendar from showing future months. |
| **B. Third-Party JS Datepicker Library (Flatpickr, Air Datepicker)** | Heavy JS library with custom CSS. | Increases page weight; introduces external script dependencies; conflicts with Tailwind styling and pure vanilla PHP portability design. |
| **C. Backend-Only Validation (PHP `header('Location: ...')`)** | Validate only upon form submit. | Poor user experience; forces candidate through 4 wizard steps before reporting date errors on Step 3 or 4. |
| **D. HTML5 Native `max` with Bidirectional Sync (Selected Approach)** | Native browser calendar constraints + lightweight event listeners. | Zero external dependencies; instant native calendar greying-out of future months/days; works across all mobile and desktop browsers seamlessly. |

---

## 5. WHAT WENT WRONG & HOW IT WAS DIAGNOSED

1. **Inverted Constraint Identification**:
   - *Symptom*: In the screenshot, `DATE FROM` showed `12/04/2026` and `DATE TO` was `10/30/2026`.
   - *Investigation*: Inspecting `index.php` revealed `<input type="date" name="date_to[]" min="<?php echo $tomorrowDate; ?>">`.
   - *Root Cause*: `min` attribute was mistakenly used instead of `max`. Because `min` requires dates $\ge$ tomorrow, the browser only allowed future dates and rejected past dates.
   - *Fix*: Replaced `min="$tomorrowDate"` with `max="$todayDate"` on both `date_from[]` and `date_to[]`.
2. **Missing Cross-Field Minimum Constraint**:
   - *Symptom*: Candidates could enter sign-off dates (`DATE TO`) that were earlier than sign-on dates (`DATE FROM`).
   - *Fix*: Implemented `syncExperienceDateConstraints` so choosing `DATE FROM` dynamically injects `min` into `DATE TO`.

---

## 6. VERIFICATION & TESTING

1. **PHP Syntax Verification**:
   - `php -l Crystal_App_Form/index.php` -> Syntax check passed (`No syntax errors detected`).
   - `php -l questionnaire/Crystal_AppForm.php` -> Syntax check passed (`No syntax errors detected`).
2. **Frontend Build Verification**:
   - React component `ApplicationFormPage.tsx` updated with `max` on DOB and verified.
3. **Browser Constraint Behavior**:
   - In modern browsers (Chromium / Firefox / WebKit), `<input type="date" max="2026-10-09">` physically disables all days after October 9, 2026 in the calendar picker.
   - Manual keyboard typing of future dates triggers HTML5 `rangeOverflow` and is blocked by `validateExperiences()` and `goToNextStep()`.

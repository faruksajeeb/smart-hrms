All changes have been made. Summary:

1. Routes (routes/web.php):
   - Wrapped all HR routes in `Route::prefix('hr')->name('hr.')->middleware('role:hr')->group(...)`
   - Added resources for `weekly-off-assignments` and `shift-assignments` (excluding show)
   - Maintained existing routes for weekly-off-policies

2. Navigation (resources/js/Layouts/HRLayout.jsx):
   - Added 'hr.weekly-off-assignments.*' and 'hr.weekly-off-assignments.*' to Attendance active array
   - Added two new children under Attendance:
        * Weekly Off Assignments (route: 'hr.weekly-off-assignments.index')
        * Shift Assignments (route: 'hr.shift-assignments.index')

3. Controllers:
   - EmployeeWeeklyOffAssignmentController & EmployeeShiftAssignmentController:
     * Changed to top-level resources (no employee in URL)
     * Added authorization gates
     * Get employee from request (`$request->employee_id`) for store/update
     * Index returns all assignments with eager-loaded relationships
     * Create/edit get all employees and policies/shifts for forms
     * Removed history methods (not implemented in frontend)

4. Services:
   - EmployeeWeeklyOffAssignmentService & EmployeeShiftAssignmentService:
     * Store: validates no overlap, ends current assignment, creates new
     * Update: same validation, adjusts if effective date changes
     * Delete: soft delete
     * getAvailablePolicies/getAvailableShifts: returns active policies/shifts

5. Frontend (resources/js/Pages/HR/Employee*Assignments/):
   - Index.jsx: Table showing assignments (employee, dates, policy/shift, type, remarks) with pagination and action buttons
   - Create/Edit.jsx: Form wrappers
   - Form.jsx:
        * Employee dropdown (populated from employees prop)
        * Policy/Shift dropdown (populated from policies/shifts prop)
        * Date inputs (effective_from, effective_to)
        * Assignment type select (initial/transfer/promotion/temporary/manual)
        * Remarks textarea
        * Default effective_set to today for new entries
        * Validation error display
        * Submit/reset logic

6. Models:
   - EmployeeShiftAssignment.php: Fixed employee relationship to use `user_id` (was `employee_id`)
   - EmployeeWeeklyOffAssignment.php: Already correct
   - WeeklyOffPolicy.php & WeeklyOffPolicyDay.php: Already correct

To complete setup:
1. Run migrations: `php artisan migrate`
2. Optionally seed policies: `php artisan db:seed --class=WeeklyOffPolicySeeder`
3. Build frontend: `npm run dev` (development) or `npm run build` (production)
4. Access via HR menu → Attendance → Weekly Off Assignments / Shift Assignments

All CRUD operations (list, create, edit, delete) are now functional for both assignment types under the Attendance menu.
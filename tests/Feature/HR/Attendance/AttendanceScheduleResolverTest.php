<?php

use App\Models\User;
use App\Services\HR\AttendanceScheduleResolver;
use Carbon\Carbon;

test('attendance schedule resolver provides classification key for working days', function () {
    $employee = User::factory()->create();

    $resolver = app(AttendanceScheduleResolver::class);
    $schedule = $resolver->resolve($employee, Carbon::today());

    expect($schedule)->toHaveKey('classification');
    expect($schedule['classification'])->toBe('WORKING_DAY');
});

<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\ExerciseHelper;
use App\Models\User;
use App\Models\Exercise;
use App\Services\SolutionChecker;
use Illuminate\Database\Eloquent\Model;

class ExerciseService
{
    public function __construct(private SolutionChecker $checker, private ActivityService $activityService)
    {
    }

    public function check(User $user, Exercise $exercise, string $solutionCode): CheckResult
    {
        if (!ExerciseHelper::exerciseHasTests($exercise)) {
            $this->completeExercise($activityService);
            return new CheckResult(0, '');
        }

        $checkResult = $this->checker->check($user, $exercise, $solutionCode);

        if ($checkResult->isSuccess()) {
            $this->completeExercise($user, $exercise);
        }

        return $checkResult;
    }

    public function completeExercise(User $user, Exercise $exercise): void
    {
        if ($user->hasCompletedExercise($exercise)) {
            return;
        }

        $user->exercises()->syncWithoutDetaching($exercise);
        $this->activityService->logCompletedExercise($user, $exercise);
    }

    // TODO: remove me
    public function removeCompletedExercise($user, $exercise)
    {
        $user->exercises()->detach($exercise);
        $this->activityService->logRemovedExercise($user, $exercise);
    }

    public function createSolution(User $user, Exercise $exercise, string $solutionCode): void
    {
        if (empty($solutionCode)) {
            return;
        }

        $solution = new Solution(['content' => $solutionCode]);

        $solution = $solution->user()->associate($user);
        $solution = $solution->exercise()->associate($exercise);
        $solution->save();

        $this->activityService->logAddedSolution($user, $solution);
    }
}

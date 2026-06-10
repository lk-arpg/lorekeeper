<?php

namespace App\Services;

use App\Models\Criteria\Criterion;
use App\Models\Criteria\CriterionDefault;
use App\Models\Criteria\CriterionStep;
use App\Models\Criteria\CriterionStepOption;
use App\Models\Criteria\DefaultCriteria;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CriterionService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Criterion Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of criteria.
    |
    */

    /**
     * Create a criterion.
     *
     * @param array $data
     *
     * @return \App\Models\Criterion\Criterion|bool
     */
    public function createCriterion($data) {
        DB::beginTransaction();

        try {
            if (!isset($data['round_precision']) || !$data['round_precision']) {
                $data['round_precision'] = 1;
            }
            $criterion = Criterion::create($data);
            $this->handleActive(isset($data['is_active']), $criterion);
            $criterion->save();

            return $this->commitReturn($criterion);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update a criterion.
     *
     * @param array $data
     * @param mixed $criterion
     *
     * @return \App\Models\Criterion\Criterion|bool
     */
    public function updateCriterion($criterion, $data) {
        DB::beginTransaction();

        try {
            if (!isset($data['round_precision']) || !$data['round_precision']) {
                $data['round_precision'] = 1;
            }
            if (isset($data['sort'])) {
                $this->handleSort($data['sort'], CriterionStep::class);
            }
            $this->handleActive(isset($data['is_active']), $criterion);
            if (isset($data['is_guide_active'])) {
                $criterion->is_guide_active = 1;
            } else {
                $criterion->is_guide_active = 0;
            }

            $criterion->update($data);
            $criterion->save();

            return $this->commitReturn($criterion);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Create a criterion Step.
     *
     * @param array $data
     *
     * @return \App\Models\Criterion\Criterion|bool
     */
    public function createCriterionStep($data) {
        DB::beginTransaction();

        try {
            if (isset($data['description']) && $data['description']) {
                $data['parsed_description'] = parse($data['description']);
            }

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $step = CriterionStep::create($data);
            $this->handleActive(isset($data['is_active']), $step);

            if ($image) {
                $this->handleImage($image, $step->imagePath, $step->imageFileName);
            }

            if ($data['type'] === 'input' || $data['type'] === 'boolean') {
                // For these two types we need to default create a single option to represent some additional data
                $step->input_calc_type = 'multiplicative';

                CriterionStepOption::create([
                    'name'              => $data['type'],
                    'amount'            => 1,
                    'is_active'         => 1,
                    'criterion_step_id' => $step->id,
                ]);
            }

            $step->save();

            return $this->commitReturn($step);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update a criterion step.
     *
     * @param array $data
     * @param mixed $step
     *
     * @return \App\Models\Criterion\Criterion|bool
     */
    public function updateCriterionStep($step, $data) {
        DB::beginTransaction();

        try {
            // If we switch type, we want to just delete the existing options cause they're likely not relevant anymore
            if ($step->type !== $data['type']) {
                foreach ($step->options as $option) {
                    $this->deleteCriterionOption($option);
                }

                if ($data['type'] === 'input' || $data['type'] === 'boolean') {
                    // For these two types we need to default create a single option to represent some additional data
                    $step->input_calc_type = 'multiplicative';

                    CriterionStepOption::create([
                        'name'              => $data['type'],
                        'amount'            => 1,
                        'is_active'         => 1,
                        'criterion_step_id' => $step->id,
                    ]);
                }
            } elseif ($data['type'] === 'input' || $data['type'] === 'boolean') {
                $optionData = $data['options'];
                $option = CriterionStepOption::where('id', $optionData['id'])->first();
                $option->update($optionData);
                $option->save();
            } else {
                if (isset($data['sort'])) {
                    $this->handleSort($data['sort'], CriterionStepOption::class);
                }
            }

            if (isset($data['description']) && $data['description']) {
                $data['parsed_description'] = parse($data['description']);
            } else {
                $data['parsed_description'] = '';
            }

            if (isset($data['remove_image'])) {
                if ($step && $step->has_image && $data['remove_image']) {
                    $data['has_image'] = 0;
                    $this->deleteImage($step->imagePath, $step->imageFileName);
                }
                unset($data['remove_image']);
            }

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            if ($step) {
                $this->handleImage($image, $step->imagePath, $step->imageFileName);
            }

            $step->update($data);
            $this->handleActive(isset($data['is_active']), $step);

            $step->save();

            return $this->commitReturn($step);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Create a criterion option.
     *
     * @param array $data
     *
     * @return \App\Models\Criterion\Criterion|bool
     */
    public function createCriterionOption($data) {
        DB::beginTransaction();

        try {
            if (isset($data['description']) && $data['description']) {
                $data['parsed_description'] = parse($data['description']);
            }
            $option = CriterionStepOption::create($data);
            $this->handleActive(isset($data['is_active']), $option);

            $option->save();

            return $this->commitReturn($option);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update a criterion option.
     *
     * @param array $data
     * @param mixed $option
     *
     * @return \App\Models\Criterion\Criterion|bool
     */
    public function updateCriterionOption($option, $data) {
        DB::beginTransaction();

        try {
            if (isset($data['description']) && $data['description']) {
                $data['parsed_description'] = parse($data['description']);
            }
            $this->handleActive(isset($data['is_active']), $option);
            $option->update($data);
            $option->save();

            return $this->commitReturn($option);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /** Deletes a Criterion (and it's associated steps and options) */
    public function deleteCriterion($criterion) {
        DB::beginTransaction();

        try {
            $steps = $criterion->steps;

            foreach ($steps as $step) {
                $this->deleteCriterionStep($step);
            }

            $criterion->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /** Deletes a step (and it's associated options) */
    public function deleteCriterionStep($step) {
        DB::beginTransaction();

        try {
            $options = $step->options;

            foreach ($options as $option) {
                $this->deleteCriterionOption($option);
            }

            $step->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /** Deletes an option */
    public function deleteCriterionOption($option) {
        DB::beginTransaction();

        try {
            $option->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /** Populates criterion relationships for prompts, galleries, etc. */
    public function populateCriteria($data, $entity, $relationshipClass) {
        // clear out old relationships
        $entity->criteria()->delete();

        // letting the two of them coexist if need be
        if (isset($data['default_criteria'])) {
            foreach (array_filter($data['default_criteria']) as $key => $toggle) {
                $default = CriterionDefault::find($key);
                foreach ($default->criteria as $criterion) {
                    $relationshipClass::create([
                        // so it can be prompt_id or gallery_id
                        strtolower(class_basename($entity)).'_id' => $entity->id,
                        'criterion_id'                            => $criterion->criterion->id,
                        'min_requirements'                        => $criterion->minRequirements,
                        'criterion_currency_id'                   => $criterion->criterion_currency_id ?? null,
                    ]);
                }
            }
        }
        if (isset($data['criterion_id'])) {
            foreach ($data['criterion_id'] as $key => $criterionId) {
                $relationshipClass::create([
                    // so it can be prompt_id or gallery_id
                    strtolower(class_basename($entity)).'_id' => $entity->id,
                    'criterion_id'                            => $criterionId,
                    'min_requirements'                        => $data['criterion'][$criterionId] ?? null,
                    'criterion_currency_id'                   => $data['criterion_currency_id'][$criterionId] ?? null,
                ]);
            }
        }
    }

    // defaults

    /**
     * Create a default.
     *
     * @param array $data
     *
     * @return \App\Models\Criterion\Criterion|bool
     */
    public function createCriterionDefault($data) {
        DB::beginTransaction();

        try {
            $default = CriterionDefault::create($data);

            $this->populateCriteria(Arr::only($data, ['criterion_id', 'criterion']), $default, DefaultCriteria::class);

            return $this->commitReturn($default);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update a default.
     *
     * @param array $data
     * @param mixed $default
     *
     * @return \App\Models\Criterion\Criterion|bool
     */
    public function updateCriterionDefault($default, $data) {
        DB::beginTransaction();

        try {
            $default->update($data);
            $default->save();

            $this->populateCriteria(Arr::only($data, ['criterion_id', 'criterion', 'criterion_currency_id']), $default, DefaultCriteria::class);

            return $this->commitReturn($default);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /** Deletes a Criterion (and it's associated steps and options) */
    public function deleteCriterionDefault($default) {
        DB::beginTransaction();

        try {
            $default->criteria()->delete();

            $default->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Handles saving the sorting order for steps.
     *
     * @param mixed $sort
     * @param mixed $model
     */
    private function handleSort($sort, $model) {
        $ids = explode(',', $sort);
        $steps = $model::whereIn('id', $ids)->orderBy(DB::raw('FIELD(id, '.implode(',', $ids).')'))->get();

        if (count($steps) != count($ids)) {
            throw new \Exception('Invalid step included in sorting order.');
        }

        $count = 0;
        foreach ($steps as $step) {
            $step->order = $count;
            $step->save();
            $count++;
        }
    }

    /** Handles setting the active setting on a given object */
    private function handleActive($isActive, $object) {
        if ($isActive) {
            $object->is_active = 1;
        } else {
            $object->is_active = 0;
        }
    }
}

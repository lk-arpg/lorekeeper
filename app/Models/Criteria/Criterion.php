<?php

namespace App\Models\Criteria;

use App\Models\Currency\Currency;
use App\Models\Model;

class Criterion extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'currency_id', 'is_active', 'summary', 'is_guide_active', 'base_value', 'rounding', 'round_precision',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'criteria';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'        => 'required|unique:criteria|between:3,100',
        'currency_id' => 'required',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name' => 'required|between:3,100',
    ];

    /**********************************************************************************************

        RELATIONS

     **********************************************************************************************/

    /**
     * Get the currency for this criterion.
     */
    public function currency() {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    /**
     * Get all steps associated with the criterion.
     */
    public function steps() {
        return $this->hasMany(CriterionStep::class, 'criterion_id')->orderBy('order');
    }

    /**********************************************************************************************

        ACCESSORS

     **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return $this->is_guide_active ? '<a href="'.url('criteria/guide/'.$this->id).'" class="display-species">'.$this->name.'</a>' : $this->name;
    }

    /**********************************************************************************************

        SCOPES

     **********************************************************************************************/

    /**
     * Scope a query to only include visible criteria.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where('is_active', 1);
    }

    /**********************************************************************************************

        METHODS

     **********************************************************************************************/

    public function calculateReward($stepResults) {
        $total = $this->base_value ?? 0;

        if (isset($stepResults)) {
            foreach ($this->steps->where('is_active', 1) as $step) {
                $subTotal = 0;
                // Get our subtotal from the step type and options
                if ($step->type === 'boolean' && isset($stepResults[$step->id])) {
                    $subTotal = $step->options->first()->amount;
                } elseif ($step->type === 'input') {
                    $subTotal = $step->options->first()->amount;
                    $input = floatval($stepResults[$step->id] ?? 0);
                    if ($step->input_calc_type === 'additive') {
                        $subTotal += $input;
                    } elseif ($step->input_calc_type === 'multiplicative') {
                        $isDivision = $subTotal < 0;

                        if (!$isDivision) {
                            $subTotal *= $input;
                        } elseif ($input === 0 && $isDivision) {
                            throw new \Exception('Criterion attempted to divide by zero.');
                        } else {
                            $subTotal = $input / -$subTotal;
                        }
                    }
                } elseif ($step->type === 'options') {
                    $optionId = $stepResults[$step->id] ?? null;
                    $option = $step->options()->where('id', $optionId)->first();
                    $subTotal = isset($option) ? $option->amount : 0;
                }

                // Apply subtotal to running total based on calc type
                if ($step->calc_type === 'additive') {
                    $total += $subTotal;
                } elseif ($step->calc_type === 'multiplicative') {
                    $isDivision = $subTotal < 0;
                    // makes sure we don't zero out the equation if we have an unset multiplicative boolean
                    if (!$isDivision) {
                        $total *= max($subTotal, 1);
                    } elseif ($subTotal === 0 && $isDivision) {
                        throw new \Exception('Criterion attempted to divide by zero.');
                    } else {
                        $total /= -$subTotal;
                    }
                }
            }
        }

        $precision = $this->round_precision - 1;
        if ($this->rounding === 'Traditional Rounding') {
            $total = round($total, -$precision);
        } elseif ($this->rounding === 'Always Rounds Up') {
            $total = $this->ceil_plus($total, -$precision);
        } elseif ($this->rounding === 'Always Rounds Down') {
            $total = $this->floor_plus($total, -$precision);
        }

        return $total;
    }

    private function ceil_plus(float $value, ?int $precision = null): float {
        if ($precision === null) {
            return (float) ceil($value);
        }

        $reg = $value + 0.5 / (10 ** $precision);

        return round($reg, $precision, $reg > 0 ? PHP_ROUND_HALF_DOWN : PHP_ROUND_HALF_UP);
    }

    private function floor_plus(float $value, ?int $precision = null): float {
        if ($precision === null) {
            return (float) floor($value);
        }

        $reg = $value - 0.5 / (10 ** $precision);

        return round($reg, $precision, $reg > 0 ? PHP_ROUND_HALF_UP : PHP_ROUND_HALF_DOWN);
    }
}

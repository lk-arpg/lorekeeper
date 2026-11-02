<?php

namespace App\Models\Currency;

use App\Models\Model;

class CurrencyConversion extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'currency_id', 'conversion_id', 'rate',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'currency_conversions';
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'currency_id'         => 'required|exists:currencies,id',
        'conversion_id'       => 'required|exists:currencies,id',
        'rate'                => 'required|numeric',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'currency_id'         => 'required|exists:currencies,id',
        'conversion_id'       => 'required|exists:currencies,id',
        'rate'                => 'required|numeric',
    ];

    /**********************************************************************************************

        RELATIONSHIPS

    **********************************************************************************************/

    /**
     * Get the currency that the conversion is for.
     */
    public function currency() {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    /**
     * Get the currency that is converted to.
     */
    public function convert() {
        return $this->belongsTo(Currency::class, 'conversion_id');
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Gets the ratio based on the decimal conversion rate.
     *
     * @param mixed $return
     */
    public function ratio($return = false) {
        $numerator = $this->rate * 100; // Convert rate to avoid floating point issues
        $denominator = 100;
        $divisor = $this->gcd($numerator, $denominator); // Find GCD to simplify ratio

        // Simplify the ratio
        $numerator /= $divisor;
        $denominator /= $divisor;

        if ($return) {
            return [$numerator, $denominator];
        }

        return $numerator.':'.$denominator;
    }

    /**
     * Gets the greatest common divisor of two numbers.
     *
     * @param mixed $a
     * @param mixed $b
     */
    private function gcd($a, $b) {
        return $b ? $this->gcd($b, $a % $b) : $a;
    }
}

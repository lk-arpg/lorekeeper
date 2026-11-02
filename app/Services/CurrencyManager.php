<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Models\Character\CharacterCurrency;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyConversion;
use App\Models\User\User;
use App\Models\User\UserCurrency;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CurrencyManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Currency Service
    |--------------------------------------------------------------------------
    |
    | Handles the modification of currencies owned by users and characters.
    |
    */

    /**
     * Admin function for granting currency to multiple users.
     *
     * @param array $data
     * @param User  $staff
     *
     * @return bool
     */
    public function grantUserCurrencies($data, $staff) {
        DB::beginTransaction();

        try {
            if ($data['quantity'] == 0) {
                throw new \Exception('Please enter a non-zero quantity.');
            }

            // Process names
            $users = User::find($data['names']);
            if (count($users) != count($data['names'])) {
                throw new \Exception('An invalid user was selected.');
            }

            // Process currency
            $currency = Currency::find($data['currency_id']);
            if (!$currency) {
                throw new \Exception('Invalid currency selected.');
            }
            if (!$currency->is_user_owned) {
                throw new \Exception('This currency cannot be held by users.');
            }

            if ($data['quantity'] < 0) {
                foreach ($users as $user) {
                    if (!$this->logAdminAction($staff, 'Currency Debit', 'Debited '.$data['quantity'].' '.$currency->displayName.' from '.$user->displayname)) {
                        throw new \Exception('Failed to log admin action.');
                    }
                    $this->debitCurrency($user, $staff, 'Staff Removal', $data['data'], $currency, -$data['quantity']);
                    Notifications::create('CURRENCY_REMOVAL', $user, [
                        'currency_name'     => $currency->name,
                        'currency_quantity' => -$data['quantity'],
                        'sender_url'        => $staff->url,
                        'sender_name'       => $staff->name,
                    ]);
                }
            } else {
                foreach ($users as $user) {
                    if (!$this->logAdminAction($staff, 'Currency Grant', 'Granted '.$data['quantity'].' '.$currency->displayName.' to '.$user->displayname)) {
                        throw new \Exception('Failed to log admin action.');
                    }
                    $this->creditCurrency($staff, $user, 'Staff Grant', $data['data'], $currency, $data['quantity']);
                    Notifications::create('CURRENCY_GRANT', $user, [
                        'currency_name'     => $currency->name,
                        'currency_quantity' => $data['quantity'],
                        'sender_url'        => $staff->url,
                        'sender_name'       => $staff->name,
                    ]);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Admin function for granting currency to a character.
     * Removes currency if the quantity given is less than 0.
     *
     * @param array                           $data
     * @param \App\Models\Character\Character $staff
     * @param User                            $staff
     * @param mixed                           $character
     *
     * @return bool
     */
    public function grantCharacterCurrencies($data, $character, $staff) {
        DB::beginTransaction();

        try {
            if ($data['quantity'] == 0) {
                throw new \Exception('Please enter a non-zero quantity.');
            }

            if (!$character) {
                throw new \Exception('Invalid character selected.');
            }

            // Process currency
            $currency = Currency::find($data['currency_id']);
            if (!$currency) {
                throw new \Exception('Invalid currency selected.');
            }
            if (!$currency->is_character_owned) {
                throw new \Exception('This currency cannot be held by characters.');
            }
            if ($data['quantity'] < 0) {
                $this->debitCurrency($character, $staff, 'Staff Removal', $data['data'], $currency, -$data['quantity']);
                if (isset($character->user)) {
                    Notifications::create('CHARACTER_CURRENCY_REMOVAL', $character->user, [
                        'currency_name'     => $currency->name,
                        'currency_quantity' => -$data['quantity'],
                        'sender_url'        => $staff->url,
                        'sender_name'       => $staff->name,
                        'character_name'    => $character->fullName,
                        'character_slug'    => $character->slug,
                    ]);
                }
            } else {
                $this->creditCurrency($staff, $character, 'Staff Grant', $data['data'], $currency, $data['quantity']);
                if (isset($character->user)) {
                    Notifications::create('CHARACTER_CURRENCY_GRANT', $character->user, [
                        'currency_name'     => $currency->name,
                        'currency_quantity' => $data['quantity'],
                        'sender_url'        => $staff->url,
                        'sender_name'       => $staff->name,
                        'character_name'    => $character->fullName,
                        'character_slug'    => $character->slug,
                    ]);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Transfers currency between users.
     *
     * @param User     $sender
     * @param User     $recipient
     * @param Currency $currency
     * @param int      $quantity
     *
     * @return bool
     */
    public function transferCurrency($sender, $recipient, $currency, $quantity) {
        DB::beginTransaction();

        try {
            if (!$recipient) {
                throw new \Exception('Invalid recipient selected.');
            }
            if ($recipient->logType == 'User' && !$recipient->hasAlias) {
                throw new \Exception('Cannot transfer currency to a non-verified member.');
            }
            if ($recipient->logType == 'User' && $recipient->is_banned) {
                throw new \Exception('Cannot transfer currency to a banned member.');
            }
            if (!$currency) {
                throw new \Exception('Invalid currency selected.');
            }
            if ($quantity <= 0) {
                throw new \Exception('Invalid quantity entered.');
            }

            if ($this->debitCurrency($sender, $recipient, null, null, $currency, $quantity) &&
            $this->creditCurrency($sender, $recipient, null, null, $currency, $quantity)) {
                $this->createLog($sender->id, $sender->logType, $recipient->id, $recipient->logType, 'User Transfer', null, $currency->id, $quantity);

                Notifications::create('CURRENCY_TRANSFER', $recipient, [
                    'currency_name'     => $currency->name,
                    'currency_quantity' => $quantity,
                    'sender_url'        => $sender->url,
                    'sender_name'       => $sender->name,
                ]);

                return $this->commitReturn(true);
            }
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Transfers currency between a user and character.
     *
     * @param \App\Models\Character\Character|User $sender
     * @param \App\Models\Character\Character|User $recipient
     * @param Currency                             $currency
     * @param int                                  $quantity
     *
     * @return bool
     */
    public function transferCharacterCurrency($sender, $recipient, $currency, $quantity) {
        DB::beginTransaction();

        try {
            if (!$recipient) {
                throw new \Exception('Invalid recipient selected.');
            }
            if (!$sender) {
                throw new \Exception('Invalid sender selected.');
            }
            if ($recipient->logType == 'Character' && $sender->logType == 'Character') {
                throw new \Exception('Cannot transfer currencies between characters.');
            }
            if ($recipient->logType == 'Character' && !$sender->hasPower('edit_inventories') && !$recipient->is_visible) {
                throw new \Exception('Invalid character selected.');
            }
            if (!$currency) {
                throw new \Exception('Invalid currency selected.');
            }
            if ($quantity <= 0) {
                throw new \Exception('Invalid quantity entered.');
            }

            if ($this->debitCurrency($sender, $recipient, null, null, $currency, $quantity) &&
            $this->creditCurrency($sender, $recipient, null, null, $currency, $quantity)) {
                $this->createLog($sender->id, $sender->logType, $recipient->id, $recipient->logType, $sender->logType == 'User' ? 'User → Character Transfer' : 'Character → User Transfer', null, $currency->id, $quantity);

                return $this->commitReturn(true);
            }
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Credits currency to a user or character.
     *
     * @param \App\Models\Character\Character|User $sender
     * @param \App\Models\Character\Character|User $recipient
     * @param string                               $type
     * @param string                               $data
     * @param Currency                             $currency
     * @param int                                  $quantity
     *
     * @return bool
     */
    public function creditCurrency($sender, $recipient, $type, $data, $currency, $quantity) {
        DB::beginTransaction();

        try {
            if (is_numeric($currency)) {
                $currency = Currency::find($currency);
            }
            if ($recipient->logType == 'User') {
                $record = UserCurrency::where('user_id', $recipient->id)->where('currency_id', $currency->id)->first();
                if ($record) {
                    // Laravel doesn't support composite primary keys, so directly updating the DB row here
                    DB::table('user_currencies')->where('user_id', $recipient->id)->where('currency_id', $currency->id)->update(['quantity' => $record->quantity + $quantity]);
                } else {
                    $record = UserCurrency::create(['user_id' => $recipient->id, 'currency_id' => $currency->id, 'quantity' => $quantity]);
                }
            } else {
                $record = CharacterCurrency::where('character_id', $recipient->id)->where('currency_id', $currency->id)->first();
                if ($record) {
                    // Laravel doesn't support composite primary keys, so directly updating the DB row here
                    DB::table('character_currencies')->where('character_id', $recipient->id)->where('currency_id', $currency->id)->update(['quantity' => $record->quantity + $quantity]);
                } else {
                    $record = CharacterCurrency::create(['character_id' => $recipient->id, 'currency_id' => $currency->id, 'quantity' => $quantity]);
                }
            }
            if ($type && !$this->createLog(
                $sender ? $sender->id : null,
                $sender ? $sender->logType : null,
                $recipient ? $recipient->id : null,
                $recipient ? $recipient->logType : null,
                $type,
                $data,
                $currency->id,
                $quantity
            )) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Debits currency from a user or character.
     *
     * @param \App\Models\Character\Character|User $sender
     * @param \App\Models\Character\Character|User $recipient
     * @param string                               $type
     * @param string                               $data
     * @param Currency                             $currency
     * @param int                                  $quantity
     *
     * @return bool
     */
    public function debitCurrency($sender, $recipient, $type, $data, $currency, $quantity) {
        DB::beginTransaction();

        try {
            if ($sender->logType == 'User') {
                $record = UserCurrency::where('user_id', $sender->id)->where('currency_id', $currency->id)->first();
                if (!$record || $record->quantity < $quantity) {
                    throw new \Exception('Not enough '.$currency->name.' to carry out this action.');
                }

                // Laravel doesn't support composite primary keys, so directly updating the DB row here
                DB::table('user_currencies')->where('user_id', $sender->id)->where('currency_id', $currency->id)->update(['quantity' => $record->quantity - $quantity]);
            } else {
                $record = CharacterCurrency::where('character_id', $sender->id)->where('currency_id', $currency->id)->first();
                if (!$record || $record->quantity < $quantity) {
                    throw new \Exception('Not enough '.$currency->name.' to carry out this action.');
                }

                // Laravel doesn't support composite primary keys, so directly updating the DB row here
                DB::table('character_currencies')->where('character_id', $sender->id)->where('currency_id', $currency->id)->update(['quantity' => $record->quantity - $quantity]);
            }

            if ($type && !$this->createLog(
                $sender ? $sender->id : null,
                $sender ? $sender->logType : null,
                $recipient ? $recipient->id : null,
                $recipient ? $recipient->logType : null,
                $type,
                $data,
                $currency->id,
                -$quantity
            )) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Converts currency from one type to another.
     *
     * @param Currency $currency
     * @param Currency $conversion
     * @param int      $quantity
     * @param User     $user
     *
     * @return bool
     */
    public function convertCurrency($currency, $conversion, $quantity, $user) {
        DB::beginTransaction();

        try {
            if (!$currency) {
                throw new \Exception('Invalid currency selected.');
            }
            if (!$conversion) {
                throw new \Exception('Invalid conversion selected.');
            }
            if ($quantity <= 0) {
                throw new \Exception('Invalid quantity entered.');
            }

            // first make sure that quantity is in the appropriate ratio
            $conversion_rate = CurrencyConversion::where('currency_id', $currency->id)->where('conversion_id', $conversion->id)->first();

            if (!$conversion_rate) {
                throw new \Exception('Invalid conversion.');
            }

            $ratio = $conversion_rate->ratio(true);

            // make sure the quantity is in the appropriate ratio
            if ($quantity % $ratio[0] != 0) {
                throw new \Exception('Invalid quantity entered, must be in multiples of '.$ratio[0].'.');
            }

            // make sure the user has enough of the currency to convert
            $record = UserCurrency::where('user_id', $user->id)->where('currency_id', $currency->id)->first();
            if (!$record || $record->quantity < $quantity) {
                throw new \Exception('Not enough '.$currency->name.' to carry out this action.');
            }

            // save log since we change quantity later
            $log = 'Converted '.$quantity.' '.$currency->displayName.' to '.$conversion->displayName;

            // remove the currency
            if (!$this->debitCurrency($user, null, 'Currency Conversion', $log, $currency, $quantity)) {
                throw new \Exception('Failed to debit currency.');
            }

            $quantity = intval($quantity / $ratio[0] * $ratio[1]);

            // add the converted currency
            if (!$this->creditCurrency(null, $user, 'Currency Conversion', $log, $conversion, $quantity)) {
                throw new \Exception('Failed to credit currency.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates a currency log.
     *
     * @param int    $senderId
     * @param string $senderType
     * @param int    $recipientId
     * @param string $recipientType
     * @param string $type
     * @param string $data
     * @param int    $currencyId
     * @param int    $quantity
     *
     * @return int
     */
    public function createLog($senderId, $senderType, $recipientId, $recipientType, $type, $data, $currencyId, $quantity) {
        return DB::table('currencies_log')->insert(
            [
                'sender_id'      => $senderId,
                'sender_type'    => $senderType,
                'recipient_id'   => $recipientId,
                'recipient_type' => $recipientType,
                'log'            => $type.($data ? ' ('.$data.')' : ''),
                'log_type'       => $type,
                'data'           => $data, // this should be just a string
                'currency_id'    => $currencyId,
                'quantity'       => $quantity,
                'created_at'     => Carbon::now(),
                'updated_at'     => Carbon::now(),
            ]
        );
    }
}

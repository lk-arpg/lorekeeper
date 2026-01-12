<?php

namespace App\Actions\Fortify;

use App\Models\Invitation;
use App\Models\Rank\Rank;
use App\Models\User\User;
use App\Services\InvitationService;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers {
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @return \App\Models\User
     */
    public function create(array $input) {
        (new UserService)->validator($input)->validate();

        // Create a user with the lowest existing rank
        $rank_id = Rank::orderBy('sort')->first()->id;

        $user = User::create([
            'name'     => $input['name'],
            'email'    => $input['email'],
            'password' => Hash::make($input['password']),
            'rank_id'  => $rank_id,
            'birthday' => $input['dob'],
        ]);
        $user->settings()->create([
            'user_id' => $user->id,
        ]);
        $user->profile()->create([
            'user_id' => $user->id,
        ]);

        if (isset($input['code'])) {
            if (!(new InvitationService)->useInvitation(Invitation::where('code', $input['code'])->whereNull('recipient_id')->first(), $user)) {
                throw new \Exception('An error occurred while using the invitation code.');
            }
        }

        return $user;
    }
}

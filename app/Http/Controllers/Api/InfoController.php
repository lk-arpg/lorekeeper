<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use Illuminate\Http\Request;

class InfoController extends Controller {
    public function getCharacter(Request $request) {
        $character = Character::find($request->id);

        if ($character) {
            return response()->json($character);
        } else {
            return response()->json([
                'message' => 'No character found.',
            ], 404);
        }
    }
}

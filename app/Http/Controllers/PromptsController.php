<?php

namespace App\Http\Controllers;

use App\Models\Prompt\Prompt;
use App\Models\Prompt\PromptCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromptsController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Prompts Controller
    |--------------------------------------------------------------------------
    |
    | Displays information about prompts as entered in the admin panel.
    | Pages displayed by this controller form the Prompts section of the site.
    |
    */

    /**
     * Shows the index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('prompts.index');
    }

    /**
     * Shows the prompt categories page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPromptCategories(Request $request) {
        $query = PromptCategory::query();
        $name = $request->get('name');
        if ($name) {
            $query->where('name', 'LIKE', '%'.$name.'%');
        }

        return view('prompts.prompt_categories', [
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the prompts page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPrompts(Request $request) {
        $query = Prompt::active()->staffOnly(Auth::user() ?? null)->with('category');
        $data = $request->only(['prompt_category_id', 'name', 'sort', 'open_prompts']);
        if (isset($data['prompt_category_id']) && $data['prompt_category_id'] != 'none') {
            if ($data['prompt_category_id'] == 'withoutOption') {
                $query->whereNull('prompt_category_id');
            } else {
                $query->where('prompt_category_id', $data['prompt_category_id']);
            }
        }
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }

        if (isset($data['open_prompts'])) {
            switch ($data['open_prompts']) {
                case 'open':
                    $query->open(true);
                    break;
                case 'closed':
                    $query->open(false);
                    break;
                case 'any':
                default:
                    // Don't filter
                    break;
            }
        }

        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'category':
                    $query->sortCategory();
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortNewest(true);
                    break;
                case 'start':
                    $query->sortStart();
                    break;
                case 'start-reverse':
                    $query->sortStart(true);
                    break;
                case 'end':
                    $query->sortEnd();
                    break;
                case 'end-reverse':
                    $query->sortEnd(true);
                    break;
            }
        } else {
            $query->sortCategory();
        }

        return view('prompts.prompts', [
            'prompts'    => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + ['withoutOption' => 'Without Category'] + PromptCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows an individual prompt.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPrompt(Request $request, $id) {
        $prompt = Prompt::active()->where('id', $id)->first();

        if (!$prompt) {
            abort(404);
        }

        return view('prompts.prompt', [
            'prompt' => $prompt,
        ]);
    }
}

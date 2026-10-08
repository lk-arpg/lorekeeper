<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class AdminEdit extends Component {
    /** @var object The model object. */
    public object $object;

    /** @var string The title for the button. */
    public string $title;

    /**
     * Create a new component instance.
     *
     * @param mixed $title
     * @param mixed $object
     */
    public function __construct($title, $object) {
        $this->title = $title;
        $this->object = $object;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Closure|\Illuminate\Contracts\View\View|string
     */
    public function render() {
        if (Auth::check() && Auth::user()->hasPower($this->object->adminPower)) {
            return view('components.admin-edit');
        } else {
            return '';
        }
    }
}

<?php

namespace App\Helpers;

class FormAdapter {
    // The following code is adapted from alban/laravel-collective-spatie-html-parser, under the following license:
    // The MIT License (MIT)
    //
    // Copyright © 2024 Christian Albán
    //
    // Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
    //
    // The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
    //
    // THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

    public function checkbox($name, $value = 1, $checked = null, $options = []) {
        $element = html()->checkbox($name, $checked, $value);

        return $this->mergeOptions($element, $options);
    }

    public function open(array $options = []) {
        $method = array_key_exists('method', $options) ? $options['method'] : 'POST';
        $route = array_key_exists('route', $options) ? $options['route'] : '';
        $files = array_key_exists('files', $options) ? $options['files'] : false;

        unset($options['method'], $options['route'], $options['files']);

        $form = html();

        if (is_array($route) && count($route)) {
            $action = array_shift($route);
            $form = $form->form($method, route($action, $route));
        } elseif ($route != null && $route != [] && $route != '') {
            $form = $form->form($method, route($route));
        } else {
            $form = $form->form($method);
        }

        if ($files) {
            $form = $form->acceptsFiles();
        }

        return $this->mergeOptions($form, $options)->open();
    }

    public function label($name, $value = null, $options = [], $escape_html = true) {
        // Since many labels in LK lack a value, this assumes that a single input is the label text and should be displayed accordingly
        $element = html()->label($value ?? $name, $value ? $name : null);

        return $this->mergeOptions($element, $options);
    }

    public function text($name, $value = null, $options = []) {
        $element = html()->text($name, $value);

        return $this->mergeOptions($element, $options);
    }

    public function password($name, $options = []) {
        $element = html()->password($name);

        return $this->mergeOptions($element, $options);
    }

    public function select(
        $name,
        $list = [],
        $selected = null,
        array $selectAttributes = [],
        array $optionsAttributes = [],
        array $optgroupsAttributes = []
    ) {
        if (isset($selectAttributes['multiple']) || in_array('multiple', $selectAttributes)) {
            $element = html()->select($name, $list, $selected)->multiple();
        } else {
            $element = html()->select($name, $list, $selected);
        }

        return $this->mergeOptions($element, $selectAttributes);
    }

    public function radio($name, $value = null, $checked = null, $options = []) {
        $element = html()->radio($name, $checked, $value);

        return $this->mergeOptions($element, $options);
    }

    public function submit($value = null, $options = []) {
        $element = html()->submit($value);

        return $this->mergeOptions($element, $options);
    }

    public function close() {
        html()->endModel();

        return html()->form()->close();
    }

    public function input($type, $name, $value = null, $options = []) {
        $element = html()->input($type, $name, $value);

        return $this->mergeOptions($element, $options);
    }

    public function search($name, $value = null, $options = []) {
        return $this->input('search', $name, $value, $options);
    }

    public function model($model, array $options = []) {
        html()->model($model);

        return $this->open($options);
    }

    public function hidden($name, $value = null, $options = []) {
        $element = html()->hidden($name, $value);

        return $this->mergeOptions($element, $options);
    }

    public function email($name, $value = null, $options = []) {
        $element = html()->email($name, $value);

        return $this->mergeOptions($element, $options);
    }

    public function tel($name, $value = null, $options = []) {
        $element = html()->tel($name, $value);

        return $this->mergeOptions($element, $options);
    }

    public function number($name, $value = null, $options = []) {
        return $this->input('number', $name, $value, $options);
    }

    public function date($name, $value = null, $options = []) {
        $element = html()->date($name, $value);

        return $this->mergeOptions($element, $options);
    }

    public function datetime($name, $value = null, $options = []) {
        return $this->input('datetime', $name, $value, $options);
    }

    public function datetimeLocal($name, $value = null, $options = []) {
        return $this->input('datetime-local', $name, $value, $options);
    }

    public function time($name, $value = null, $options = []) {
        $element = html()->time($name, $value);

        return $this->mergeOptions($element, $options);
    }

    public function url($name, $value = null, $options = []) {
        return $this->input('url', $name, $value, $options);
    }

    public function file($name, $options = []) {
        $element = html()->file($name);

        return $this->mergeOptions($element, $options);
    }

    public function textarea($name, $value = null, $options = []) {
        $element = html()->textarea($name, $value);

        return $this->mergeOptions($element, $options);
    }

    public function reset($value, $attributes = []) {
        $element = html()->reset($value);

        return $this->mergeOptions($element, $attributes);
    }

    public function image($url, $name = null, $attributes = []) {
        $element = html()->img($url, $name);

        return $this->mergeOptions($element, $attributes);
    }

    public function color($name, $value = null, $options = []) {
        return $this->input('color', $name, $value, $options);
    }

    public function button($value = null, $options = []) {
        $element = html()->button($value);

        return $this->mergeOptions($element, $options);
    }

    private function mergeOptions($element, $options = []) {
        $newElement = $element;

        if (isset($options['class'])) {
            $newElement = $newElement->addClass($options['class']);
            unset($options['class']);
        }

        if (isset($options['placeholder'])) {
            $newElement = $newElement->placeholder($options['placeholder']);
            unset($options['placeholder']);
        }

        foreach ($options as $key => $value) {
            if (!$value) {
                continue;
            }

            if (is_numeric($key)) {
                $newElement = $newElement->attribute($value, '');
                continue;
            }

            $newElement = $newElement->attribute($key, $value);
        }

        return $newElement;
    }
}

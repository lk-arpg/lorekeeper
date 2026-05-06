<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ParsePostRequestFields {
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response) $next
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next) {
        if ($request->isMethod('post')) {
            $excludedFields = ['_token', 'password', 'email', 'description', 'text', 'criteria'];
            $strippedFields = ['name', 'title'];

            $parsedFields = [];
            foreach ($request->except($excludedFields) as $key => $value) {
                if (is_array($value)) {
                    $parsedFields[$key] = $this->parseArray($value, $strippedFields);
                } else {
                    if (is_numeric($value)) {
                        continue;
                    }

                    if (in_array($key, $strippedFields)) { // we strip these since parse() doesn't remove HTML tags
                        $parsedFields[$key] = parse(strip_tags($value));
                    } else {
                        $parsedFields[$key] = parse($value);
                    }

                    // Decode HTML special chars
                    if ($parsedFields[$key] != null) {
                        $parsedFields[$key] = htmlspecialchars_decode($parsedFields[$key]);
                    }
                }
            }

            $request->merge($parsedFields);
        }

        return $next($request);
    }

    /**
     * Recursively parse array values.
     */
    private function parseArray(array $array, array $strippedFields): array {
        foreach ($array as $key => $value) {
            if (is_numeric($value)) {
                continue;
            }

            if (is_array($value)) {
                $array[$key] = $this->parseArray($value, $strippedFields);
            } else {
                if (in_array($key, $strippedFields)) {
                    $array[$key] = parse(strip_tags($value));
                } else {
                    $array[$key] = parse($value);
                }

                // Decode HTML special chars
                if ($array[$key] != null) {
                    $array[$key] = htmlspecialchars_decode($array[$key]);
                }
            }
        }

        return $array;
    }
}

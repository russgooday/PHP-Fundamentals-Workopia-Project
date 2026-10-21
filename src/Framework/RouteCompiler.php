<?php

namespace Framework;


class RouteCompiler {

    const PARAM = '#(?P<separators>[/.,;:_~+*=@|-])?\{(?P<param>[^}]*)\}#';

    const NEXT_SEP = '#^(?:\{[^}]*\})*(?P<separator>[.,;:_~+*=@|-])#';

    const PARAM_FLAGS = PREG_OFFSET_CAPTURE | PREG_SET_ORDER;


    static public function compilePattern(string $route, $patterns = [], $defaults = []) {
        $params = [];
        $tokens = [];
        $curr_index = 0;

        foreach (regex_match_all(self::PARAM, $route, self::PARAM_FLAGS) as $match) {

            [$full_match, $match_start] = $match[0];

            $separator = $match['separators'][0];
            $param = $match['param'][0];

            if ($param === '') {
                throw new \LogicException("Route parameter name cannot be empty");
            }

            if (in_array($param, $params)) {
                throw new \LogicException("Cannot repeat parameter name '$param' in route");
            }

            if ($text = substr($route, $curr_index, $match_start - $curr_index)) {
                $tokens[] = ['text' => $text];
            }

            // update the current index to the end of the matched parameter
            $curr_index = $match_start + strlen($full_match);

            $rest_of_route = substr($route, $curr_index);
            $next_separator = regex_match(SELF::NEXT_SEP, $rest_of_route)['separator'] ?? '';

            $props = [
                'separator' => $separator,
                'param' => $param,
                'pattern' => $patterns[$param] ?? "[^/$next_separator]+"
            ];

            if (array_key_exists($param, $defaults)) {
                $props['default'] = $defaults[$param];
            }

            $tokens[] = $props;
            $params[] = $param;
        }

        // mop up
        if ($curr_index < strlen($route)) {
            $tokens[] = ['text' => substr($route, $curr_index)];
        }

        return self::buildPattern(self::markOptionals($tokens));
    }


    static public function buildPattern(array $tokens): string {
        $closing_optionals = [];
        $pattern = '#^';

        foreach($tokens as $token) {

            if (isset($token['text'])) {
                $pattern .= preg_quote($token['text'], '#');
                continue;
            }

            $separator = preg_quote($token['separator'], '#');
            $param = $token['param'];
            $param_pattern = $token['pattern'];

            if (isset($token['optional'])) {

                $pattern .= "(?:$separator(?P<$param>$param_pattern)";
                $closing_optionals[] = ')?';

            } else {

                $pattern .= "$separator(?P<$param>$param_pattern)";
            }
        }

        return $pattern . implode('', $closing_optionals) . "$#D";
    }


    /**
     * Marks tokens as optional if they have default values.
     * @param array $tokens The array of route tokens.
     * @return array The array of tokens with optional flags set.
     *
     * Tokens are processed in reverse order. Processing stops if a
     * token without a default value is encountered
     * e.g. /{a}/b/{c} where 'a' and 'c' have default values,
     * only 'c' will be marked as optional.
     */
    static private function markOptionals(array $tokens) : array {
        foreach (array_reverse($tokens, true) as $i => $token) {

            if (!array_key_exists('default', $token)) break;

            $tokens[$i]['optional'] = true;
        }
        return $tokens;
    }
}
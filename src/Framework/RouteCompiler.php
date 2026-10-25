<?php

namespace Framework;


class RouteCompiler {

    const PARAM = '#(?P<separators>[/.,;:_~+*=@|-])?\{(?P<param>[^}]*)\}#';

    const NEXT_SEP = '#^(?:\{[^}]*\})*(?P<separator>[.,;:_~+*=@|-])#';

    const PARAM_FLAGS = PREG_OFFSET_CAPTURE | PREG_SET_ORDER;


    /**
     * Compiles a route string into a regular expression pattern.
     *
     * @param string $route The route pattern to compile.
     * @param array $patterns Optional array of parameter patterns.
     * @param array $defaults Optional array of default parameter values.
     * @return string The compiled regular expression pattern.
     */
    static public function compilePattern(string $route, $patterns = [], $defaults = []): string {
        return self::compile(self::tokenize($route, $patterns, $defaults));
    }


    /**
     * Tokenizes a route string into an array of tokens.
     * Each token represents either a static text segment or a route parameter.
     *
     * @param string $route The route pattern to tokenize.
     * @param array $patterns Optional array of parameter patterns.
     * @param array $defaults Optional array of default parameter values.
     * @return array The array of tokens representing the route.
     */
    static public function tokenize(string $route, $patterns = [], $defaults = []): array {

        $params = []; // used to check for duplicate parameters
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

        return self::markOptionals($tokens);
    }


    /**
     * Compiles an array of route tokens into a regular expression pattern.
     *
     * @param array $tokens The array of route tokens.
     * @return string The compiled regular expression pattern.
     */
    static public function compile(array $tokens): string {
        $closing_optionals = [];
        $pattern = '#^';
        $getProps = itemGetter('separator', 'param', 'pattern');

        foreach($tokens as $token) {

            if (isset($token['text'])) {
                $pattern .= preg_quote($token['text'], '#');
                continue;
            }

            [$separator, $param, $param_pattern] = $getProps($token);

            $separator = preg_quote($separator, '#');

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
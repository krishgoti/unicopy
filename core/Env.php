<?php
/**
 * UniHelp - Env Loader
 * Simple class to load .env variables into $_ENV and getenv()
 */

class Env {
    private static array $repository = [];

    /**
     * Load environment variables from a file.
     * 
     * @param string $path Path to the .env file
     * @param string $prefix Prefix to prepend to keys
     * @return bool True if loaded successfully
     */
    public static function load(string $path, string $prefix = ''): bool {
        if (!file_exists($path)) {
            return false;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments and lines without equals sign
            if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                continue;
            }

            // Parse key=value
            list($name, $value) = explode('=', $line, 2);
            $name  = $prefix . trim($name);
            $value = trim($value);

            // Strip inline comments if not inside quotes
            if (!str_starts_with($value, '"') && !str_starts_with($value, "'")) {
                $value = explode('#', $value, 2)[0];
            }
            $value = trim($value);

            // Strip optional quotes
            if (preg_match('/^"(.*)"$/', $value, $matches) || preg_match("/^'(.*)'$/", $value, $matches)) {
                $value = $matches[1];
            }

            // Save in internal repository (guarantees local .env precedence over system env)
            self::$repository[$name] = $value;

            // Set environment variable
            putenv("$name=$value");
            $_ENV[$name]    = $value;
            $_SERVER[$name] = $value;
        }
        return true;
    }

    /**
     * Get an environment variable with a fallback.
     * 
     * @param string $key The key to find
     * @param mixed $default Fallback value
     * @return mixed
     */
    public static function get(string $key, $default = null) {
        if (array_key_exists($key, self::$repository)) {
            return self::$repository[$key];
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        $value = getenv($key);
        if ($value === false || $value === '') {
            return $default;
        }
        return $value;
    }
}

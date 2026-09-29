<?php

namespace Tests\Support;

use Illuminate\Contracts\Config\Repository;
use RuntimeException;

final class TestDatabaseSafety
{
    /**
     * Refuse to run destructive database test helpers against non-test databases.
     */
    public static function assertAllConnectionsAreForTesting(Repository $config): void
    {
        if ($config->get('app.env') !== 'testing') {
            throw new RuntimeException('Database tests may only run when APP_ENV=testing.');
        }

        $unsafe = [];

        foreach ($config->get('database.connections', []) as $name => $connection) {
            $driver = $connection['driver'] ?? null;

            if (! in_array($driver, ['mysql', 'mariadb', 'pgsql', 'sqlsrv', 'sqlite'], true)) {
                continue;
            }

            $database = (string) ($connection['database'] ?? '');

            if ($driver === 'sqlite') {
                if ($database !== ':memory:' && ! self::isClearlyTestDatabase($database)) {
                    $unsafe[] = "{$name}={$database}";
                }

                continue;
            }

            if (! self::isClearlyTestDatabase($database)) {
                $unsafe[] = "{$name}={$database}";
            }
        }

        if ($unsafe === []) {
            return;
        }

        throw new RuntimeException(
            "Refusing to refresh databases that are not clearly dedicated to testing:\n- "
            .implode("\n- ", $unsafe)
            ."\nUse database names containing the standalone word 'test' or 'testing' "
            .'(for example laravel_testing), or SQLite :memory:.'
        );
    }

    private static function isClearlyTestDatabase(string $database): bool
    {
        return preg_match('~(^|[._/\\\\-])test(?:ing)?($|[._/\\\\-])~i', $database) === 1;
    }
}

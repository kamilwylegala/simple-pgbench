<?php

$host = 'pgsql:host=localhost;port=5432;dbname=benchmark_db';
$user = 'benchmark_user';
$pass = 'benchmark_pass';

$pdo = new \PDO($host, $user, $pass, [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);

$iterations = 1;

function measureTime($callback)
{
    $start = microtime(true);
    $callback();
    $end = microtime(true);

    $diff = $end - $start;
    echo "Execution time: ".($diff)." seconds\n";

    return $diff;
}

echo "Connected successfully\n";

//Get random project ids from DB
$projectIds = $pdo->query(
    'select project_id from benchmark_data group by project_id order by random() limit 10;'
)->fetchAll(\PDO::FETCH_COLUMN);

foreach ($projectIds as $projectId) {
    echo "### Project id: $projectId\n";

    echo "Running total count\n";
    for ($i = 0; $i < $iterations; $i++) {
        measureTime(
            fn () => $pdo->prepare('select count(*) from benchmark_data where project_id = :projectId')
                ->execute([':projectId' => $projectId])
        );
    }

    echo "Total count by project id\n";
    for ($i = 0; $i < $iterations; $i++) {
        measureTime(
            fn () => $pdo
                ->prepare(
                    'select project_id, count(*) from benchmark_data where project_id = :projectId group by project_id'
                )
                ->execute([
                              ':projectId' => $projectId,
                          ])
        );
    }

    echo "Sum all by project_id\n";
    for ($i = 0; $i < $iterations; $i++) {
        measureTime(
            fn () => $pdo
                ->prepare(
                    'select project_id, sum(value) from benchmark_data where project_id = :projectId group by project_id'
                )->execute([':projectId' => $projectId])
        );
    }

    echo "Aggregate by project_id, metric name and aggregate via sum\n";
    for ($i = 0; $i < $iterations; $i++) {
        measureTime(
            fn () => $pdo->prepare(
                'select project_id, name, sum(value) from benchmark_data where project_id = :projectId group by project_id, name'
            )->execute([':projectId' => $projectId])
        );
        echo "SQL: select project_id, name, sum(value) from benchmark_data where project_id = '$projectId' group by project_id, name\n";
    }

    echo "Aggregate by project_id, metrics_name, in march 2025 and sum\n";
    for ($i = 0; $i < $iterations; $i++) {
        measureTime(
            fn () => $pdo->prepare(
                'select project_id, name, sum(value) from benchmark_data where project_id = :projectId and created_at >= \'2025-03-01\' and created_at < \'2025-04-01\' group by project_id, name'
            )->execute([':projectId' => $projectId])
        );
    }

    echo "\n";
}

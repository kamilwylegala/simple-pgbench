<?php

use Ramsey\Uuid\Uuid;

require_once __DIR__.'/vendor/autoload.php';

$host = 'pgsql:host=localhost;port=5432;dbname=benchmark_db';
$user = 'benchmark_user';
$pass = 'benchmark_pass';

$availableProjectIds = [];
foreach (range(1, 100) as $index) {
    $availableProjectIds[] = Uuid::uuid7()->toString();
}

$availableDates = [];
foreach (range(1, 100) as $index) {
    $availableDates[] = date('Y-m-d H:i:s', strtotime('-'.$index.' days'));
}

try {
    $pdo = new \PDO($host, $user, $pass, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ]);

    echo "Connected successfully\n";

    // Create table if not exists
    $pdo->exec(
        "
        CREATE EXTENSION IF NOT EXISTS \"uuid-ossp\";
        CREATE TABLE IF NOT EXISTS benchmark_data (
            id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
            project_id UUID NOT NULL,
            name TEXT NOT NULL,
            value INTEGER NOT NULL CHECK (value > 0),
            created_at TIMESTAMPTZ DEFAULT NOW()
        );
    "
    );

    $names = ['processed_words', 'hosted_words', 'projects'];
    $insert = $pdo->prepare(
        "INSERT INTO benchmark_data (id, name, project_id, value, created_at) VALUES (:id,:name, :project_id, :value, :created_at)"
    );

    for ($i = 0; $i < 10000000; $i++) {
        $projectId = $availableProjectIds[array_rand($availableProjectIds)];

        $name = $names[array_rand($names)];
        $value = rand(1, 100);
        $insert->execute([
                             ':id' => Uuid::uuid7()->toString(),
                             ':name' => $name,
                             ':project_id' => $projectId,
                             ':value' => $value,
                             ':created_at' => $availableDates[array_rand($availableDates)],
                         ]);

        // Optional: Print progress every 10000 rows
        if (($i + 1) % 10000 === 0) {
            echo "Inserted ".($i + 1)." rows...\n";
        }
    }

    echo "Finished inserting 500000 rows.\n";
} catch (\PDOException $e) {
    echo "Error: ".$e->getMessage()."\n";
}

Experimenting with `pgbench` on my local machine and checking how well postgres can handle data aggregations.

1. Run `docker-compose up -d` to start the postgres container.
2. Run `php populate.php` to populate the database with test data. It will produce 10kk rows.
3. Install pgbench: `sudo apt install postgresql-contrib`.

### Indexes to add

```sql
create index project_id_name
    on benchmark_data (project_id, name, id) include (value);
```

Including `created_at` column:
```sql
CREATE INDEX idx_project_id_created_at_name_id
ON benchmark_data (project_id, created_at, name, id DESC)
INCLUDE (value);
```

Without indexes the cases below gives approximately 7tps, example result:
```
transaction type: last-month-for-project.sql
scaling factor: 1
query mode: simple
number of clients: 10
number of threads: 3
duration: 30 s
number of transactions actually processed: 218
latency average = 1427.702 ms
initial connection time = 52.021 ms
tps = 7.004261 (without initial connection time)
```

Or `pgbench` just makes the DB crash with "no space left on disk".

## Test cases

### Case 1

Aggregating all data aggregated by `project_id`, `name` and having a sum:
```
pgbench -j 3 -c 10 -T 30 -f aggregate-all-for-project.sql -h localhost -p 5432 -U benchmark_user -d benchmark_db
```

Results:
```
transaction type: aggregate-all-for-project.sql
scaling factor: 1
query mode: simple
number of clients: 10
number of threads: 3
duration: 30 s
number of transactions actually processed: 8345
latency average = 35.931 ms
initial connection time = 57.114 ms
tps = 278.310201 (without initial connection time)
```


### Case 2

Taking latest inserted value for single `project_id` and each `name`:
```
pgbench -j 3 -c 10 -T 30 -f last-all-for-project.sql -h localhost -p 5432 -U benchmark_user -d benchmark_db
```

Results:
```
transaction type: last-all-for-project.sql
scaling factor: 1
query mode: simple
number of clients: 10
number of threads: 3
duration: 30 s
number of transactions actually processed: 3333
latency average = 90.102 ms
initial connection time = 53.710 ms
tps = 110.985718 (without initial connection time)
```


### Case 3

Taking latest inserted value but query is **limited** to specific month:
```
pgbench -j 3 -c 10 -T 30 -f last-month-for-project.sql -h localhost -p 5432 -U benchmark_user -d benchmark_db
```

Results:
```
transaction type: last-month-for-project.sql
scaling factor: 1
query mode: simple
number of clients: 10
number of threads: 3
duration: 30 s
number of transactions actually processed: 4123
latency average = 72.778 ms
initial connection time = 59.622 ms
tps = 137.405087 (without initial connection time)
```

### Case 4

Sum all `name` fields, limited to specific month:
```
pgbench -j 3 -c 10 -T 30 -f sum-month-for-project.sql -h localhost -p 5432 -U benchmark_user -d benchmark_db
```

Results:
```
transaction type: sum-month-for-project.sql
scaling factor: 1
query mode: simple
number of clients: 10
number of threads: 3
duration: 30 s
number of transactions actually processed: 20793
latency average = 14.410 ms
initial connection time = 48.247 ms
tps = 693.942353 (without initial connection time)
```

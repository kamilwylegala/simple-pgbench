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

Before each case, run: `./restore-db.sh` to restore the database to a clean state. After that wait a 1 minute.

### Case 1

Aggregating all data aggregated by `project_id`, `name` and having a sum:
```
pgbench -j 3 -c 10 -T 30 -f sum-all-for-project.sql -h localhost -p 5432 -U benchmark_user -d benchmark_db
```

Running right after restore:
```
transaction type: sum-all-for-project.sql
scaling factor: 1
query mode: simple
number of clients: 10
number of threads: 3
duration: 30 s
number of transactions actually processed: 430
latency average = 707.501 ms
initial connection time = 57.616 ms
tps = 14.134263 (without initial connection time)
```

After 1 minute:
```
transaction type: sum-all-for-project.sql
scaling factor: 1
query mode: simple
number of clients: 10
number of threads: 3
duration: 30 s
number of transactions actually processed: 11964
latency average = 25.047 ms
initial connection time = 53.365 ms
tps = 399.251114 (without initial connection time)
```


### Case 2

Taking latest inserted value for single `project_id` and each `name`:
```
pgbench -j 3 -c 10 -T 30 -f last-all-for-project.sql -h localhost -p 5432 -U benchmark_user -d benchmark_db
```

After restore:
```
transaction type: last-all-for-project.sql
scaling factor: 1
query mode: simple
number of clients: 10
number of threads: 3
duration: 30 s
number of transactions actually processed: 300
latency average = 1018.109 ms
initial connection time = 54.961 ms
tps = 9.822129 (without initial connection time)
```

After 1 minute:
```
transaction type: last-all-for-project.sql
scaling factor: 1
query mode: simple
number of clients: 10
number of threads: 3
duration: 30 s
number of transactions actually processed: 3510
latency average = 85.532 ms
initial connection time = 47.438 ms
tps = 116.914683 (without initial connection time)
```

### Case 3

Taking latest inserted value but query is **limited** to specific month:
```
pgbench -j 3 -c 10 -T 30 -f last-month-for-project.sql -h localhost -p 5432 -U benchmark_user -d benchmark_db
```

After restore:
```
transaction type: last-month-for-project.sql
scaling factor: 1
query mode: simple
number of clients: 10
number of threads: 3
duration: 30 s
number of transactions actually processed: 1357
latency average = 222.301 ms
initial connection time = 52.788 ms
tps = 44.984093 (without initial connection time
```

After 1 minute:
```
transaction type: last-month-for-project.sql
scaling factor: 1
query mode: simple
number of clients: 10
number of threads: 3
duration: 30 s
number of transactions actually processed: 4613
latency average = 65.089 ms
initial connection time = 49.777 ms
tps = 153.636905 (without initial connection time)
```

### Case 4

Sum all `name` fields, limited to specific month:
```
pgbench -j 3 -c 10 -T 30 -f sum-month-for-project.sql -h localhost -p 5432 -U benchmark_user -d benchmark_db
```

After restore:
```
transaction type: sum-month-for-project.sql
scaling factor: 1
query mode: simple
number of clients: 10
number of threads: 3
duration: 30 s
number of transactions actually processed: 1750
latency average = 171.628 ms
initial connection time = 55.909 ms
tps = 58.265664 (without initial connection time)
```

After 1 minute:
```
transaction type: sum-month-for-project.sql
scaling factor: 1
query mode: simple
number of clients: 10
number of threads: 3
duration: 30 s
number of transactions actually processed: 24047
latency average = 12.456 ms
initial connection time = 54.127 ms
tps = 802.812283 (without initial connection time)
```

### My setup

Test cases were run on my local machine with the following specs:

- 32GB memory
- 12 CPU cores

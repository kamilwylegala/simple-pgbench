select project_id, name, sum(value) from benchmark_data where project_id = '019662fc-ffa5-721b-b2c3-cd07c5b55539' group by project_id, name;

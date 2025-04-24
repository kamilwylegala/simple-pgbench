select distinct on (project_id, name) project_id, name, value
from benchmark_data
where project_id = '019662fc-ffa5-721b-b2c3-cd07c5b55539'
order by project_id, name, id desc;

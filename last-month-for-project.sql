SELECT DISTINCT ON (project_id, name) project_id, name, value
FROM benchmark_data
WHERE project_id = '019662fc-ffa5-721b-b2c3-cd07c5b55539'
  AND created_at >= '2025-02-01'
  AND created_at < '2025-03-01'
ORDER BY project_id, name, id DESC;

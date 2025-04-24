SELECT project_id, name, SUM(value)
FROM benchmark_data
WHERE project_id = '019662fc-ffa5-721b-b2c3-cd07c5b55539'
  AND created_at >= '2025-02-01'
  AND created_at < '2025-03-01'
  GROUP BY project_id, name

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

CREATE TABLE IF NOT EXISTS benchmark_data (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id UUID NOT NULL,
    name TEXT NOT NULL,
    value INTEGER NOT NULL CHECK (value > 0),
    created_at TIMESTAMPTZ DEFAULT NOW()
);


create index project_id_name
    on benchmark_data (project_id, name, id) include (value);

CREATE INDEX idx_project_id_created_at_name_id
ON benchmark_data (project_id, created_at, name, id DESC)
INCLUDE (value);

#!/bin/bash

# Define variables
CONTAINER_NAME="postgres_benchmark"
CSV_FILE="file.csv"
DB_NAME="benchmark_db"
TABLE_NAME="benchmark_data"

# Check if the CSV file exists
if [ ! -f "$CSV_FILE" ]; then
  echo "Error: $CSV_FILE does not exist in the current directory."
  exit 1
fi

# Copy the CSV file to the container
docker cp "$CSV_FILE" "$CONTAINER_NAME:/tmp/$CSV_FILE"
if [ $? -ne 0 ]; then
  echo "Error: Failed to copy $CSV_FILE to the container."
  exit 1
fi

# Copy schema SQL file to the container
docker cp "schema.sql" "$CONTAINER_NAME:/tmp/schema.sql"

# Drop current table if it exists
docker exec -i "$CONTAINER_NAME" psql -U benchmark_user -d "$DB_NAME" -c "DROP TABLE IF EXISTS $TABLE_NAME;"

# Execute schema.sql
docker exec -i "$CONTAINER_NAME" psql -U benchmark_user -d "$DB_NAME" -f /tmp/schema.sql

echo "Copying data from $CSV_FILE to $TABLE_NAME..."

# Execute the COPY command inside the container
docker exec -i "$CONTAINER_NAME" psql -U benchmark_user -d "$DB_NAME" -c "\COPY $TABLE_NAME FROM '/tmp/$CSV_FILE' WITH CSV HEADER;"
if [ $? -ne 0 ]; then
  echo "Error: Failed to load data from $CSV_FILE into $TABLE_NAME."
  exit 1
fi

echo "Data from $CSV_FILE successfully loaded into $TABLE_NAME."

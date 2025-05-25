# Start from pgvector-enabled image
FROM pgvector/pgvector:pg17

# Copy init SQL into the directory PostgreSQL scans on first startup
COPY ./docker/vector-db/init-kb.sql /docker-entrypoint-initdb.d/

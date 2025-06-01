-- Enable extension (safe if it already exists)
CREATE EXTENSION IF NOT EXISTS vector;

-- Schema
CREATE TABLE IF NOT EXISTS kb_chunks (
  id       TEXT PRIMARY KEY,
  content  TEXT,
  metadata JSONB,
  embed    vector(1536)
);

-- Index for ANN search
CREATE INDEX IF NOT EXISTS kb_chunks_embed_cos
  ON kb_chunks
USING hnsw (embed vector_cosine_ops)
WITH (m = 16, ef_construction = 64);

-- Added this index for metadata repo_path and file_name
CREATE INDEX idx_repo_dir_file
    ON kb_chunks ( (metadata->>'repo_path'), (metadata->>'file_name') );

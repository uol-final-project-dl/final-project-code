-- Enable extension (safe if it already exists)
CREATE EXTENSION IF NOT EXISTS vector;

-- Schema
CREATE TABLE IF NOT EXISTS kb_chunks (
  id       TEXT PRIMARY KEY,
  content  TEXT,
  metadata JSONB,
  embed    vector(1024)
);

-- Index for ANN search
CREATE INDEX IF NOT EXISTS kb_chunks_embed_cos
  ON kb_chunks
USING hnsw (embed vector_cosine_ops)
WITH (m = 16, ef_construction = 64);

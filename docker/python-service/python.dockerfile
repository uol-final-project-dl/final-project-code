FROM python:3.13-slim

RUN apt-get update && apt-get install -y --no-install-recommends \
      build-essential \
      libglib2.0-0 libsm6 libxrender1 libxext6 \
    && rm -rf /var/lib/apt/lists/*

# Install Python libraries
RUN pip install --no-cache-dir \
      transformers[pytorch] \
      pillow \
      torch

# Small init
RUN pip install --no-cache-dir dumb-init

WORKDIR /app

COPY scripts/caption.py /app/caption.py
RUN chmod +x /app/caption.py

ENTRYPOINT ["dumb-init", "--"]
CMD ["sleep", "infinity"]

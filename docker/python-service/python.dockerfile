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

# Install colorthief
RUN pip install --no-cache-dir colorthief

RUN pip install --no-cache-dir dumb-init

WORKDIR /app

COPY scripts/caption.py /app/caption.py
COPY scripts/colors.py /app/colors.py

RUN chmod +x /app/caption.py
RUN chmod +x /app/colors.py

ENTRYPOINT ["dumb-init", "--"]
CMD ["sleep", "infinity"]

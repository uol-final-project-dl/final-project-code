My UOL Final Project
=================

To run the project, please follow these steps:

1. Clone the repository.
2. Navigate to the project directory.
3. Make sure you have Docker, Docker Compose, Composer and npm/yarn installed.
4. Run docker-compose up -d to start the Docker containers.
5. Install the required dependencies using Composer and npm/yarn:
    ```bash
   composer install
   yarn
    ```
6. Set up your environment variables by copying the .env.example file to .env and updating the necessary values (
   ```bash
   database, API keys, etc.). Run:
   docker compose run --rm artisan optimize:clear
    ```
7. Generate the application key:
   ```bash
   docker compose run --rm artisan key:generate
    ```
8. Run the database migrations and seeders:
   ```bash
   docker compose run --rm artisan migrate --seed
    ```
9. Build the frontend assets:
   ```bash
   yarn watch
    ```
10. Access the application in your web browser at http://host.docker.internal:8325.
11. Login to Minio at http://host.docker.internal:9102 with the credentials provided in the .env file.
12. Create a bucket named "brainstorm-to-prototype" on Minio.
13. Add the Minio credentials to the .env file.
14.

To run the local ollama server for open source models, use the following commands (only on Mac):

```bash
brew install ollama
ollama serve
ollama pull llama3.1:8b-instruct-q4_K_M
ollama pull qwen2.5:7b-instruct-q4_K_M
ollama pull qwen2.5-coder:7b-instruct-q4_K_M
```

Technologies used:

- Laravel
- React
- Bootstrap
- Ant Design
- MySQL
- PostgreSQL
- pgvector
- OpenAI API
- Anthropic API
- Google Cloud API
- ffmpeg
- OpenAI Whisper
- Ollama

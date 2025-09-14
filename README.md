My UOL Final Project
=================

To run the project, please follow these steps:

1. Clone the repository.
2. Navigate to the project directory.
3. Make sure you have Docker, Docker Compose, Composer and npm/yarn installed.
4. Run to start the Docker containers.
    ```bash
    docker compose up -d
     ```
5. Install the required dependencies using Composer and npm/yarn
    ```bash
   composer install
   yarn
    ```
6. Create a schema on the mysql server called "brainstorm-to-prototype" using the credentials on the evn example file.
7. Login to Minio at http://host.docker.internal:9102 with the credentials provided in the .env file.
8. Create a bucket named "uol" on Minio and a key with read and write permissions to use in the .env file.
9. Set up your environment variables by copying the .env.example file to .env and updating the necessary values (database, API keys, etc.). Run:
   ```bash
   docker compose run --rm artisan migrate  --seed
   docker compose run --rm artisan optimize:clear
    ```
10. Generate the application key:
   ```bash
   docker compose run --rm artisan key:generate
    ```
11. Build the frontend assets:
   ```bash
   yarn watch
    ```
12. Start the queue worker
    ```bash
    docker compose run --rm artisan queue:work
     ```
13. Access the application in your web browser at http://host.docker.internal:8325.
14. If your folder is not called final-project-code-main, please change it in GeneratePrototype.php file line 172.

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

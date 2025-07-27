echo "Building and running project..."
docker-compose up -d --build --force-recreate
echo "Copying .env file..."
docker exec -i api-messages cp -r .env.example .env
echo "Setting permissions..."
docker exec -i api-messages chmod 777 -R storage
echo "Installing dependencies..."
docker exec -i api-messages composer install
echo "Creating queue table..."
docker exec -i api-messages php artisan queue:table 
echo "Migrating database..."
docker exec -i api-messages php artisan migrate
echo "Generating key..."
docker exec -i api-messages php artisan key:generate
echo "Running tests..."
docker exec -i api-messages php artisan test --filter=MessageControllerTest  --stop-on-failure 
docker exec -i api-messages php artisan test --filter=ProcessarMessageTest  --stop-on-failure 
echo "Running message broker simulator. You can test the application now..."
docker exec -i api-messages php artisan queue:work

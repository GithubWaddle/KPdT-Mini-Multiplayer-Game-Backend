Proyek Akhir
Mata Kuliah Komputasi Paralel dan Terdistribusi

# !!! RUN  AFTER DOCKKER RUNNINGG ALL IMAGES
Inside Docker
docker-compose up -d --build

docker exec laravel_app php artisan key:generate
docker exec laravel_app php artisan jwt:secret
docker exec laravel_app php artisan migrate:fresh --seed

Verify it's all working
docker exec laravel_app php artisan route:list
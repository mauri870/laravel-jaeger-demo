# Jaeger Laravel Demo

> Please check the details at [my blog post](https://mauri870.github.io/blog/posts/opentracing-jaeger-laravel).

A simple demo application showing how to integrate the distributed tracing system Jaeger with a Laravel app.

First start the application and install the dependencies:

```bash
docker-compose up -d
docker-compose exec php bash
composer install
cp .env.example .env
php artisan key:generate
```

Access `http://jaeger-demo.localhost` in your browser then go to `http://jaeger-ui.localhost` and check the traces. The only modified file is the [AppServiceProvider.php](app/Providers/AppServiceProvider.php).
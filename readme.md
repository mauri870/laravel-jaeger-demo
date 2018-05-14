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

Access the following urls in your browser:

- `http://jaeger-demo.localhost`
- `http://jaeger-demo.localhost/error`
- `http://jaeger-demo.localhost/really-long-request`

Then go to `http://jaeger-ui.localhost` and check the traces. 

I keep the changes in the [AppServiceProvider.php](app/Providers/AppServiceProvider.php) and [web.php](routes/web.php) for brevity.

Try to change the search options, fill the Tags field with something like `response_status=200`, or `error=true`, `uuid=some-request-uuid` to test it out.
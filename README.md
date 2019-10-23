# laravel-dag

#Use

## lumen

add code to `bootstrap/app.php`

```php
    $app->register(DagTaskServiceProvider::class);
    $app->configure('task');
```



```php
    $graph_manager = app('dag');
    $dag_pipeline = new DagPipeline();
    
    $graph_manager->pipeline($task_name, $dag_pipeline)->then(function ($data) {
        dump($data);
    });
```
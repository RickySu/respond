# respond
Fast, async I/O web framework for php

Example

app.php

```php
<?php
use WebUtil\Http\Request\ServerRequest;

$app = new Respond\App\WebApp();

return $app->listen('0.0.0.0', 8080)
    ->get('/{id:\\d+}', function(ServerRequest $request){
        return "match id with {$request->getAttribute('id')}";
    })
    ->post('/{id:\\d+}.html', function(ServerRequest $request){
        return "match id.html {$request->getAttribute('id')}";
    })
    ->request(['GET', 'POST'], '/{id:\\d+}-{id2:\\d+}.html', function(ServerRequest $request){
        return $request->getAttribute('id');
    })
    ->defaultRequest(function(ServerRequest $request){
        return 'default';
    });

```

Execute

```
bin/respond app.php
```

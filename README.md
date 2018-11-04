Up: 
```
UID=${UID} GID=${GID} docker-compose up -d
```

Or, export these variables as environment variables, ignoring bash: UID: readonly variable
```
export UID=${UID}
export GID=${GID} 
```

ALIASES:
```
alias tf='docker-compose exec php-cli vendor/bin/phpunit --filter'
alias art='docker-compose exec php-cli php artisan'
alias dphp='docker-compose exec php-cli'
```


##### For using Laravel echo server, add the following sections 
```
// to layout.blade.php, before <script src="{{ asset('app.js') }}"></script>

<script>
  window.echoConfig = {
    host: {!! json_encode(env('ECHO_SERVER_HOST')) !!},
    port: {!! json_encode(env('ECHO_SERVER_PORT')) !!}
  };
</script>
```
```
// bootstrap.js

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

window.io = require('socket.io-client');

const host = window.echoConfig.port
  ? `${window.echoConfig.host}:${window.echoConfig.port}`
  : window.echoConfig.host;

window.Echo = new Echo({
  broadcaster: 'socket.io',
  namespace: 'App.Events.Broadcasts',
  host,
});
``` 

TODO: 
- add aliases bash script
- restructure README.md
- add all laravel required php extensions
- add debuger extension
- figure it out with deployment

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    @routes
    @vite('resources/js/app.ts')
    @inertiaHead

    <title>Renbot</title>
</head>

<body>
    @inertia
</body>

<script>
    if (navigator && navigator.serviceWorker && navigator.serviceWorker.getRegistration) {

        navigator.serviceWorker.getRegistration('/').then(function(registration) {
            if (registration) {
                registration.update();
                registration.unregister();
            }
        });

    }
</script>

</html>

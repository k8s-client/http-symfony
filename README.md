# k8s-http-symfony

This library provides a Symfony based HttpClient factory for the `k8s/client` library.

## General Use with the K8s library / Configuration Options

1. Install the library:

`composer require k8s/http-symfony`

2. Construct the main client for `k8s/client` through the `K8sFactory`:

```php
use K8s\Client\K8sFactory;

# Load the client from the default KubeConfig
$k8s = (new K8sFactory())->loadFromKubeConfig();
```

Your new client will have all the HttpClient options needed pre-populated when used.

### Default HTTP Options Configuration

To specify extra defaults for the Symfony HTTP client, you can construct it like this:

```php
use K8s\HttpSymfony\ClientFactory;
use K8s\Client\K8sFactory;

# Pass any Symfony HTTP client options here.
# The below would allow for self-signed certificates.
$httpFactory = new ClientFactory([
    'verify_peer' => false,
    'verify_host' => false,
]);

$k8s = (new K8sFactory())->loadFromKubeConfig(null, $httpFactory);
```

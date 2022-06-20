<?php

/**
 * This file is part of the k8s/http-symfony library.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace K8s\HttpSymfony;

use K8s\Core\Contract\ContextConfigInterface;
use K8s\Core\Contract\HttpClientFactoryInterface;
use Psr\Http\Client\ClientInterface;
use RuntimeException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

class ClientFactory implements HttpClientFactoryInterface
{
    /**
     * @var array
     */
    private $defaults;

    /**
     * @param array<string, mixed> $defaults Any additional default options wanted for the Symfony HttpClient.
     */
    public function __construct(array $defaults = [])
    {
        $this->defaults = $defaults;
    }

    /**
     * @inheritDoc
     */
    public function makeClient(ContextConfigInterface $fullContext, bool $isStreaming): ClientInterface
    {
        $options = $this->defaults;
        $options['timeout'] = $isStreaming ? -1 : ($options['timeout'] ?? 15);

        if ($fullContext->getClientCertificate()) {
            $options['local_cert'] = $fullContext->getClientCertificate();
        } elseif ($fullContext->getClientCertificateData()) {
            $options = $this->setCurlExtraSslDataOpt(
                $options,
                'CURLOPT_SSLCERT_BLOB',
                (string)$fullContext->getClientCertificateData()
            );
        }

        if ($fullContext->getClientKey()) {
            $options['local_pk'] = $fullContext->getClientKey();
        } elseif ($fullContext->getClientKeyData()) {
            $options = $this->setCurlExtraSslDataOpt(
                $options,
                'CURLOPT_SSLKEY_BLOB',
                (string)$fullContext->getClientKeyData()
            );
        }

        if ($fullContext->getServerCertificateAuthority()) {
            $options['cafile'] = $fullContext->getServerCertificateAuthority();
        } elseif ($fullContext->getServerCertificateAuthorityData()) {
            $options = $this->setCurlExtraSslDataOpt(
                $options,
                'CURLOPT_ISSUERCERT_BLOB',
                (string)$fullContext->getClientCertificateData()
            );
        }

        return new Psr18Client(HttpClient::createForBaseUri(
            $fullContext->getServer(),
            $options
        ));
    }

    private function setCurlExtraSslDataOpt(
        array $options,
        string $optName,
        string $value
    ): array {
        if (!defined($optName)) {
            throw new RuntimeException(sprintf(
                'Unable to set CURL option "%s". Options to set certificate data via strings requires PHP 8.1+.',
                $optName
            ));
        }
        $options['extra']['curl'][constant($optName)] = base64_decode($value);

        return $options;
    }
}

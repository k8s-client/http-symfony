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

namespace unit\K8s\HttpSymfony;

use K8s\Core\Contract\ContextConfigInterface;
use K8s\HttpSymfony\ClientFactory;
use Symfony\Component\HttpClient\Psr18Client;

class ClientFactoryTest extends TestCase
{
    /**
     * @var ClientFactory
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new ClientFactory();
    }

    public function testItCanMakeTheClient()
    {
        $fullContext = \Mockery::spy(ContextConfigInterface::class);
        $fullContext->shouldReceive([
            'getServerCertificate' => '/foo.ca',
            'getClientCertificate' => '/client.crt',
            'getClientKey' => '/client.key',
            'getServer' => 'https://foo.local:8443'
        ]);

        $result = $this->subject->makeClient($fullContext, false);
        $this->assertInstanceOf(Psr18Client::class, $result);
    }

    public function testItCanUseSslBlobDataIfProvided(): void
    {
        if (!version_compare(PHP_VERSION, '8.1.0', '>=')) {
            $this->markTestSkipped('SSL blob data options available on PHP 8.1+.');
        }

        if (!extension_loaded('curl') || !version_compare(curl_version()['version'], '7.71.0', '>=')) {
            $this->markTestSkipped('CURL extension with libcurl >= 7.71.0 required for SSL blob data options.');
        }

        $mockServerCertData = 'LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSURnakNDQW1xZ0F3SUJBZ0lFSXlZZlNqQU5CZ2txaGtpRzl3MEJBUXNGQURCYk1TY3dKUVlEVlFRRERCNVNaV2RsY25rZ1UyVnMKWmkxVGFXZHVaV1FnUTJWeWRHbG1hV05oZEdVeEl6QWhCZ05WQkFvTUdsSmxaMlZ5ZVN3Z2FIUjBjSE02THk5eVpXZGxjbmt1WTI5dApNUXN3Q1FZRFZRUUdFd0pWUVRBZ0Z3MHlNakEyTWpBd01EQXdNREJhR0E4eU1USXlNRFl5TURFME16SXdPVm93UkRFUU1BNEdBMVVFCkF3d0habTl2TG1KaGNqRWpNQ0VHQTFVRUNnd2FVbVZuWlhKNUxDQm9kSFJ3Y3pvdkwzSmxaMlZ5ZVM1amIyMHhDekFKQmdOVkJBWVQKQWxWQk1JSUJJakFOQmdrcWhraUc5dzBCQVFFRkFBT0NBUThBTUlJQkNnS0NBUUVBd0FSajFGdUJVVWpEaEtJSkVtUnp4ekhVeEJCNQpEVldiMDJjcEwyZ1VjZnlUb1UxRWRqS3B3SWx3VllMdG5idnM4L1lDb1EvQjRIR1ZLS0EyM0d6WGZTOWhJTE1RN2YrS21WTE1CTlM5Ck8xd0J6d2xSTW1ReW04ZVVzMGJacUlqUXRiZzFqRG5lVUhVNU5ZSFI3RjhLRUxldTdNRzkzbjU5TW9LYmVqc04wWVRTRjFYUXZ2T3gKWi91Ti96TVdVZzBrQVZ4YmVmUzBkMVdPZFBab1FZSEd5Q096RGRWOXJERUR0czhkRStXYy8zdEJRVGVuNElieHcvMmtONnB4aWNvVgpvK2V6VENyRVBlRWhSSjlEWXhjeUR6Kyt1NXo1YjBFa0hlZTlTV3NSNGJaRXlYSE0zS0Vqd2FkWUlzb01pTk9uUWFzbXVab0lidkYwCk54b0UzQXdVQ1FJREFRQUJvMk13WVRBUEJnTlZIUk1CQWY4RUJUQURBUUgvTUE0R0ExVWREd0VCL3dRRUF3SUJoakFkQmdOVkhRNEUKRmdRVWlkdit3M3A0SVRIN3UzNDg0eXNJM0VRSEZWd3dId1lEVlIwakJCZ3dGb0FVaWR2K3czcDRJVEg3dTM0ODR5c0kzRVFIRlZ3dwpEUVlKS29aSWh2Y05BUUVMQlFBRGdnRUJBRzlab2V4T3VXYjdTUVNQUUtqVEtJNTFjM3VsZ3dQYU5vSndYa0JwRmMwMndtTXRtc3kzCmhwdUhjbmFJLythRXJGNHpxM3BRNTVNUFpjMzRvSXlKSUhSRnRncnUyaERsbjJnVEx6SkNZWE1WMitCenJaWk9zUmIzei93VEVHMk0KejN0eEwzRkpISTRjUW9mNkpPR3ZhVG11VFlGS0l0dmxiaFN4VTZrWEFjdGdSNjBjamkzNWpETUhwTjUwTEpkb0dDdk91YnA0RTRzZAptelJBN2NIQVVMY0tXRXdxUFRtSGZleXVBV2lLaFk1cnZoZFJVT0JFU0l5czFCM3E0MjUzQW5NRzhDK3EyVXk4QVJEMVk5NUQ3VVhSCmRMZEdoNXc4L0xweTFhSU1MR0Nhd3dxWWxvaVVpdFFFT2Nnd0ZvenBNTmV5aVlwdGUwWVg2dUwwbGJPMnhOVT0KLS0tLS1FTkQgQ0VSVElGSUNBVEUtLS0tLQ==';
        $mockClientCertData = 'LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSURnakNDQW1xZ0F3SUJBZ0lFYUlLcU16QU5CZ2txaGtpRzl3MEJBUXNGQURCYk1TY3dKUVlEVlFRRERCNVNaV2RsY25rZ1UyVnMKWmkxVGFXZHVaV1FnUTJWeWRHbG1hV05oZEdVeEl6QWhCZ05WQkFvTUdsSmxaMlZ5ZVN3Z2FIUjBjSE02THk5eVpXZGxjbmt1WTI5dApNUXN3Q1FZRFZRUUdFd0pWUVRBZ0Z3MHlNakEyTWpBd01EQXdNREJhR0E4eU1USXlNRFl5TURFME16UXhNVm93UkRFUU1BNEdBMVVFCkF3d0hiV1ZvTG1KaGNqRWpNQ0VHQTFVRUNnd2FVbVZuWlhKNUxDQm9kSFJ3Y3pvdkwzSmxaMlZ5ZVM1amIyMHhDekFKQmdOVkJBWVQKQWxWQk1JSUJJakFOQmdrcWhraUc5dzBCQVFFRkFBT0NBUThBTUlJQkNnS0NBUUVBbW13WE53ZFVFSTFaNXlpL0xxUCt3QnZTUVdGZApHekNZVkMvMGxRaWtTbkRNbmZvRzhUTU5TWmlsdUEralBVMUkyR21nYlE2d01KWjlIbS9GRlhUQ0g1VFhpeDVUL1lsM001YUtYZUlHCmRObEhWR3pkOWVzMHlYcGllN2dvaWhhbWpLbFRhRHNLRFR1OU9YSS80eStEbURSLzYwNnZtWVZlLy9NVE5zUXl0Q240a2ovTHR6a00KZk5LaUtuLzZoMS9KalduWjMyU0FWSnEyd3ZBK3FjT2NlaHlCaVlIQUdoUHEzVk1ZTFNjNHVIT1dLWTZnM1ZvTGROVktheE5sendxZgpNaktNbHRQNWFBWk15VmRIamY4SEVrWnVPUnRzdEZYTVA3ci83S1RIenA0QjVleE0xN1Q5YkIzUFNrNVBJSC9BY3B5eExwTnN0cktwCndzMlpFVXorVXdJREFRQUJvMk13WVRBUEJnTlZIUk1CQWY4RUJUQURBUUgvTUE0R0ExVWREd0VCL3dRRUF3SUJoakFkQmdOVkhRNEUKRmdRVUR6SHIyZTVSOU5LQXpDblNsZzJTY2R1dVFzRXdId1lEVlIwakJCZ3dGb0FVRHpIcjJlNVI5TktBekNuU2xnMlNjZHV1UXNFdwpEUVlKS29aSWh2Y05BUUVMQlFBRGdnRUJBQW5XQW12Z2trL3g5WUxIOVIvK2tYZXc4M2F0ZCtuYlJUSGpZemFvTlNHbmEyQkJuN29oCnd4NFc2VnAyWFpzREt0TUJTN1A2TjFIdnhodW5BSnFSeXl3M2lCQ2FWem53TXNkM2ZiSS9rc2pTWUlocEFUNzRGME5SZjFxQUNjNSsKdlNkbmhTRkd1RVZCYndINjJZL3pnRTRLaWptNG9kVTNLSXpzaktHblZrdUJkZUg2clhiQ3lSR09uQUdFYk9OVWYzR2ZYTDAyV2cyTAp0T0Z5YmZqa2NSWWsxZzc1bkpDanF6RUplU3J1RHZRck1aMjVIRDlNUjkwaGc4WnkrRDhRLzlXSWFWM2loR082Mk8zT1N6anpkdEdpCjlJM3NWNXFJVUl2OU9PdXg5emlFcnBhN2xlR2d6M3BjV3k3K3lNNUp1STNSU0ZucStwMkJ3eVFGb3NmTGNMND0KLS0tLS1FTkQgQ0VSVElGSUNBVEUtLS0tLQo=';
        $mockClientCertKeyData = 'LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSURnakNDQW1xZ0F3SUJBZ0lFYUlLcU16QU5CZ2txaGtpRzl3MEJBUXNGQURCYk1TY3dKUVlEVlFRRERCNVNaV2RsY25rZ1UyVnMKWmkxVGFXZHVaV1FnUTJWeWRHbG1hV05oZEdVeEl6QWhCZ05WQkFvTUdsSmxaMlZ5ZVN3Z2FIUjBjSE02THk5eVpXZGxjbmt1WTI5dApNUXN3Q1FZRFZRUUdFd0pWUVRBZ0Z3MHlNakEyTWpBd01EQXdNREJhR0E4eU1USXlNRFl5TURFME16UXhNVm93UkRFUU1BNEdBMVVFCkF3d0hiV1ZvTG1KaGNqRWpNQ0VHQTFVRUNnd2FVbVZuWlhKNUxDQm9kSFJ3Y3pvdkwzSmxaMlZ5ZVM1amIyMHhDekFKQmdOVkJBWVQKQWxWQk1JSUJJakFOQmdrcWhraUc5dzBCQVFFRkFBT0NBUThBTUlJQkNnS0NBUUVBbW13WE53ZFVFSTFaNXlpL0xxUCt3QnZTUVdGZApHekNZVkMvMGxRaWtTbkRNbmZvRzhUTU5TWmlsdUEralBVMUkyR21nYlE2d01KWjlIbS9GRlhUQ0g1VFhpeDVUL1lsM001YUtYZUlHCmRObEhWR3pkOWVzMHlYcGllN2dvaWhhbWpLbFRhRHNLRFR1OU9YSS80eStEbURSLzYwNnZtWVZlLy9NVE5zUXl0Q240a2ovTHR6a00KZk5LaUtuLzZoMS9KalduWjMyU0FWSnEyd3ZBK3FjT2NlaHlCaVlIQUdoUHEzVk1ZTFNjNHVIT1dLWTZnM1ZvTGROVktheE5sendxZgpNaktNbHRQNWFBWk15VmRIamY4SEVrWnVPUnRzdEZYTVA3ci83S1RIenA0QjVleE0xN1Q5YkIzUFNrNVBJSC9BY3B5eExwTnN0cktwCndzMlpFVXorVXdJREFRQUJvMk13WVRBUEJnTlZIUk1CQWY4RUJUQURBUUgvTUE0R0ExVWREd0VCL3dRRUF3SUJoakFkQmdOVkhRNEUKRmdRVUR6SHIyZTVSOU5LQXpDblNsZzJTY2R1dVFzRXdId1lEVlIwakJCZ3dGb0FVRHpIcjJlNVI5TktBekNuU2xnMlNjZHV1UXNFdwpEUVlKS29aSWh2Y05BUUVMQlFBRGdnRUJBQW5XQW12Z2trL3g5WUxIOVIvK2tYZXc4M2F0ZCtuYlJUSGpZemFvTlNHbmEyQkJuN29oCnd4NFc2VnAyWFpzREt0TUJTN1A2TjFIdnhodW5BSnFSeXl3M2lCQ2FWem53TXNkM2ZiSS9rc2pTWUlocEFUNzRGME5SZjFxQUNjNSsKdlNkbmhTRkd1RVZCYndINjJZL3pnRTRLaWptNG9kVTNLSXpzaktHblZrdUJkZUg2clhiQ3lSR09uQUdFYk9OVWYzR2ZYTDAyV2cyTAp0T0Z5YmZqa2NSWWsxZzc1bkpDanF6RUplU3J1RHZRck1aMjVIRDlNUjkwaGc4WnkrRDhRLzlXSWFWM2loR082Mk8zT1N6anpkdEdpCjlJM3NWNXFJVUl2OU9PdXg5emlFcnBhN2xlR2d6M3BjV3k3K3lNNUp1STNSU0ZucStwMkJ3eVFGb3NmTGNMND0KLS0tLS1FTkQgQ0VSVElGSUNBVEUtLS0tLQo=';

        $fullContext = \Mockery::spy(ContextConfigInterface::class);
        $fullContext->shouldReceive([
            'getServerCertificate' => null,
            'getServerCertificateData' => $mockServerCertData,
            'getClientCertificate' => null,
            'getClientCertificateData' => $mockClientCertData,
            'getClientKey' => null,
            'getClientKeyData' => $mockClientCertKeyData,
            'getServer' => 'https://foo.local:8443'
        ]);

        $result = $this->subject->makeClient($fullContext, false);
        $this->assertInstanceOf(Psr18Client::class, $result);
    }
}

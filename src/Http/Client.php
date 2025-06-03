<?php

namespace TransVoucher\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use TransVoucher\Exception\ApiException;
use TransVoucher\Exception\AuthenticationException;
use TransVoucher\Exception\InvalidRequestException;
use TransVoucher\Exception\TransVoucherException;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * @var GuzzleClient
     */
    private $httpClient;

    /**
     * @var array
     */
    private $config;

    /**
     * Create a new HTTP client instance
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->httpClient = new GuzzleClient([
            'base_uri' => $config['base_url'],
            'timeout' => $config['timeout'],
            'connect_timeout' => $config['connect_timeout'],
            'headers' => [
                'User-Agent' => $config['user_agent'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-API-Key' => $config['api_key'],
                'X-API-Secret' => $config['api_secret'],
            ],
        ]);
    }

    /**
     * Make a GET request
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     * @throws TransVoucherException
     */
    public function get(string $endpoint, array $params = []): array
    {
        $options = [];
        if (!empty($params)) {
            $options['query'] = $params;
        }

        return $this->makeRequest('GET', $endpoint, $options);
    }

    /**
     * Make a POST request
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws TransVoucherException
     */
    public function post(string $endpoint, array $data = []): array
    {
        $options = [];
        if (!empty($data)) {
            $options['json'] = $data;
        }

        return $this->makeRequest('POST', $endpoint, $options);
    }

    /**
     * Make a PUT request
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws TransVoucherException
     */
    public function put(string $endpoint, array $data = []): array
    {
        $options = [];
        if (!empty($data)) {
            $options['json'] = $data;
        }

        return $this->makeRequest('PUT', $endpoint, $options);
    }

    /**
     * Make a DELETE request
     *
     * @param string $endpoint
     * @return array
     * @throws TransVoucherException
     */
    public function delete(string $endpoint): array
    {
        return $this->makeRequest('DELETE', $endpoint);
    }

    /**
     * Make an HTTP request
     *
     * @param string $method
     * @param string $endpoint
     * @param array $options
     * @return array
     * @throws TransVoucherException
     */
    private function makeRequest(string $method, string $endpoint, array $options = []): array
    {
        try {
            $response = $this->httpClient->request($method, $endpoint, $options);
            return $this->handleResponse($response);
        } catch (ConnectException $e) {
            throw new TransVoucherException(
                'Unable to connect to TransVoucher API: ' . $e->getMessage(),
                0,
                $e
            );
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        } catch (\Exception $e) {
            throw new TransVoucherException(
                'Unexpected error: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Handle successful response
     *
     * @param ResponseInterface $response
     * @return array
     * @throws TransVoucherException
     */
    private function handleResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TransVoucherException('Invalid JSON response from API');
        }

        // Check if the response indicates an error
        if (isset($data['error']) && $data['error'] === true) {
            throw new ApiException($data['message'] ?? 'Unknown API error');
        }

        return $data;
    }

    /**
     * Handle request exceptions
     *
     * @param RequestException $e
     * @throws TransVoucherException
     */
    private function handleRequestException(RequestException $e): void
    {
        $response = $e->getResponse();
        
        if (!$response) {
            throw new TransVoucherException(
                'Request failed: ' . $e->getMessage(),
                0,
                $e
            );
        }

        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $message = $data['message'] ?? $e->getMessage();

        switch ($statusCode) {
            case 401:
                throw new AuthenticationException($message, $statusCode, $e);
            case 400:
            case 422:
                throw new InvalidRequestException($message, $statusCode, $e);
            case 404:
                throw new InvalidRequestException('Resource not found', $statusCode, $e);
            case 500:
            case 502:
            case 503:
            case 504:
                throw new ApiException('Server error: ' . $message, $statusCode, $e);
            default:
                throw new TransVoucherException(
                    "HTTP {$statusCode}: {$message}",
                    $statusCode,
                    $e
                );
        }
    }
} 
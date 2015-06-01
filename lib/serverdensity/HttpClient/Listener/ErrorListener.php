<?php

namespace serverdensity\HttpClient\Listener;

use serverdensity\Exception\TwoFactorAuthenticationRequiredException;
use serverdensity\HttpClient\Message\ResponseMediator;
use serverdensity\Exception\ApiLimitExceedException;
use serverdensity\Exception\ErrorException;
use serverdensity\Exception\RuntimeException;
use serverdensity\Exception\ValidationFailedException;
use serverdensity\Exception\InvalidTokenException;
use serverdensity\Exception\NotFoundException;


use GuzzleHttp\Message\Response;
use GuzzleHttp\Event\ErrorEvent;

/**
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class ErrorListener
{
    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function onRequestError(ErrorEvent $event)
    {
        $request = $event->getRequest();
        $hasResponse = $event->hasResponse();
        if ($hasResponse){
            $response = $event->getResponse();
            if ($this->isClientError($response) || $this->isServerError($response)) {
                $remaining = (string) $response->getHeader('X-RateLimit-Remaining');

                if (null != $remaining && 1 > $remaining && 'rate_limit' !== substr($request->getResource(), 1, 10)) {
                    throw new ApiLimitExceedException($this->options['api_limit'], $request);
                }

                $content = ResponseMediator::getContent($response);
                if (is_array($content) && isset($content['message'])) {
                    if (401 == $response->getStatusCode()) {
                        throw new InvalidTokenException("Your authentication token is invalid", $request);
                    }
                    elseif (400 == $response->getStatusCode() && isset($content['errors'])) {
                        if (isset($content['errors']['subject']) && $content['errors']['subject'] === 'token'){
                            throw new InvalidTokenException("Your authentication token is missing", $request);
                        }
                        $errors = array();
                        foreach ($content['errors'] as $error) {
                            switch ($error['type']) {
                                case 'missing_param':
                                    $errors[] = sprintf("The following error occurred: '%s' for parameter '%s'", $error['description'], $error['param']);
                                    break;

                                case 'duplicate_param':
                                    $errors[] = sprintf("'%s', for parameter '%s'", $error['description'], $error['param']);
                                    break;

                                case 'invalid_param':
                                    $errors[] = sprintf("This field '%s' contains an invalid parameter; '%s'", $error['param'], $error['description']);
                                    break;

                                default:
                                    $errors[] = $content['message'];
                                    break;

                            }
                        }

                        throw new ValidationFailedException('Validation Failed: ' . implode("\n", $errors), $request, $response);
                    }

                    elseif (404 == $response->getStatusCode()){
                        throw new NotFoundException($content['message'], $request, $response);
                    }
                }
                throw new RuntimeException("Another Error Occurred: ". $content['message'], $request, $response);
            };
        }
    }

    public function isClientError($response){
        return is_null($response) ? FALSE : $response->getStatusCode() >=400 && $response->getStatusCode() < 500;
    }

    public function isServerError($response)
    {
        return is_null($response) ? FALSE : $response->getStatusCode() >= 500 && $response->getStatusCode() < 600;
    }
}

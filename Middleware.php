<?php

namespace Xe\Framework\Client\BaseClient;

final class Middleware
{
    /**
     * Middleware that attempts to authenticate and retries when a request fails with a 401 or 403 status code.
     *
     * @param callable $getAuthenticateRequestFn     Function that returns the authenticate request. The current request and options are passed as parameters
     * @param callable $handleAuthenticateResponseFn Function to handle the authenticate response. The current request, options and authenticate response are passed as parameters
     *
     * @return callable Returns a function that accepts the next handler
     */
    public static function authenticate(callable $getAuthenticateRequestFn, callable $handleAuthenticateResponseFn)
    {
        return function (callable $handler) use ($getAuthenticateRequestFn, $handleAuthenticateResponseFn) {
            return function ($request, array $options) use ($handler, $getAuthenticateRequestFn, $handleAuthenticateResponseFn) {
                $authenticateRequest = $getAuthenticateRequestFn($request, $options);

                $getCacheKeyFn = function () use ($request, $options, $authenticateRequest) {
                    if (!empty($options['cookies'])) {
                        $cookieIterator = $options['cookies']->getIterator();
                        foreach ($cookieIterator as $cookie) {
                            if (substr($cookie->getName(), 0, 3) == 'SES' || substr($cookie->getName(), 0, 4) == 'SSES') {
                                return "{$authenticateRequest->getUri()->__toString()}<{$cookie->getValue()}>";
                            }
                        }
                    }
                };

                $getAuthenticateResponseFn = function ($refresh = false) use ($handler, $request, $options, $authenticateRequest, $getCacheKeyFn) {
                    $cacheKey = $getCacheKeyFn();

                    if (isset($cacheKey)) {
                        if (!isset($_SESSION[$cacheKey]) || $refresh) {
                            if (isset($options[RequestOptions::AUTHENTICATE]) && !$options[RequestOptions::AUTHENTICATE]) {
                                return;
                            }

                            try {
                                $_SESSION[$cacheKey] = \GuzzleHttp\Psr7\str($handler($authenticateRequest, $options)->wait());
                            } catch (RequestException $e) {
                                return;
                            }
                        }

                        return \GuzzleHttp\Psr7\parse_response($_SESSION[$cacheKey]);
                    }
                };

                // Modify request with authenticate response.
                $request = $handleAuthenticateResponseFn($request, $options, $getAuthenticateResponseFn());

                return $handler($request, $options)->then(null, function (\Exception $e) use ($handler, $request, $options, $handleAuthenticateResponseFn, $getAuthenticateResponseFn) {
                    if ($e instanceof BadResponseException && in_array($e->getResponse()->getStatusCode(), [401, 403])) {
                        // Authentication error, session may have expired. Try authenticating again.
                        $request = $handleAuthenticateResponseFn($request, $options, $getAuthenticateResponseFn(true));

                        return $handler($request, $options);
                    }

                    throw $e;
                });
            };
        };
    }
}

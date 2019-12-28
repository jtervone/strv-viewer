<?php

namespace Strava;

class Client extends \League\OAuth2\Client\Provider\GenericProvider
{
    const BASE_URL = 'https://www.strava.com';

    public function __construct($options)
    {
        $options['urlAuthorize'] = self::BASE_URL.'/api/v3/oauth/authorize';
        $options['urlAccessToken'] = self::BASE_URL.'/api/v3/oauth/token';
        $options['urlResourceOwnerDetails'] = self::BASE_URL.'/api/v3/athlete';

        $this->clientId = $options['clientId'];
        $this->clientSecret = $options['clientSecret'];
        $this->redirectUri = $options['redirectUri'];
        $this->accessToken = isset($options['accessToken']) ? $options['accessToken'] : null;

        $this->options = $options;

        parent::__construct($options);
    }

    public function getActivities()
    {
        return $this->request('GET', '/api/v3/athlete/activities');
    }

    public function deauthorize()
    {
        return $this->request('POST', '/oauth/deauthorize');
    }

    public function request($method, $endpoint)
    {
        if ($this->accessToken->hasExpired()) {
            $this->accessToken = $this->getAccessToken(
                'refresh_token',
                [ 'refresh_token' => $existingAccessToken->getRefreshToken() ]
            );
        }

        try {
            $request = $this->getAuthenticatedRequest(
                $method,
                self::BASE_URL.$endpoint,
                $this->accessToken
            );
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            exit($e->getMessage());
        }

        return $this->getParsedResponse($request);
    }

    public function authenticate($code)
    {
        try {
            $this->accessToken = $this->getAccessToken(
                'authorization_code',
                [ 'code' => $_GET['code'] ]
            );
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            exit($e->getMessage());
        }

        return $this->accessToken->getValues()['athlete'];
    }

    public function getAuthorizationUrl(array $options = [])
    {
        $url = $this->options['urlAuthorize'];
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'scope' => 'activity:read'
        ];

        return $url.'?'.http_build_query($params);
    }
}

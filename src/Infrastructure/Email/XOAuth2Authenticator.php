<?php

declare(strict_types=1);

namespace App\Infrastructure\Email;

use Symfony\Component\Mailer\Transport\Smtp\Auth\AuthenticatorInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

/**
 * Adapted from Symfony\Component\Mailer\Transport\Smtp\Auth\XOAuth2Authenticator but getting the token dynamically.
 */
readonly class XOAuth2Authenticator implements AuthenticatorInterface
{
    public function __construct(
        private Office365OAuthTokenProvider $tokenProvider,
    ) {
    }

    public function getAuthKeyword(): string
    {
        return 'XOAUTH2';
    }

    /**
     * @see https://developers.google.com/google-apps/gmail/xoauth2_protocol#the_sasl_xoauth2_mechanism
     */
    public function authenticate(EsmtpTransport $client): void
    {
        try {
            $client->executeCommand('AUTH XOAUTH2 '.base64_encode('user='.$client->getUsername()."\1auth=Bearer ".$this->tokenProvider->getToken()."\1\1")."\r\n", [235]);
        } catch (\Exception $e) {
            error_log('Authentication failed: '.$e->getMessage());
            throw $e;
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Email;

use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\Auth\AuthenticatorInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;

readonly class OAuthEsmtpTransportFactoryDecorator implements TransportFactoryInterface
{
    public function __construct(
        private EsmtpTransportFactory $inner,
        private AuthenticatorInterface $authenticator,
    ) {
    }

    public function create(Dsn $dsn): TransportInterface
    {
        $transport = $this->inner->create($dsn);
        if (!$transport instanceof EsmtpTransport) {
            return $transport;
        }
        $transport->setAuthenticators([$this->authenticator]);

        return $transport;
    }

    public function supports(Dsn $dsn): bool
    {
        return $this->inner->supports($dsn);
    }
}

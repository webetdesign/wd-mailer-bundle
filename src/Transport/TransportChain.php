<?php

namespace WebEtDesign\MailerBundle\Transport;

final class TransportChain
{
    private array $transports;

    public function __construct()
    {
        $this->transports = [];
    }

    public function get($alias): ?MailTransportInterface
    {
        return $this->transports[$alias] ?? null;
    }

    public function addTransport(MailTransportInterface $transport, $alias): void
    {
        $this->transports[$alias] = $transport;
    }
}
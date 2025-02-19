<?php

namespace WebEtDesign\MailerBundle\Enum;

enum CategoryEnum: string
{
    case TRANSACTIONAL_EMAIL    = 'TRANSACTIONAL_EMAIL';
    case PRICE_CAMPAIGN_PRODUCT = 'PRICE_CAMPAIGN_PRODUCT';
    case SALE_ORDER_EMAIL       = 'SALE_ORDER_EMAIL';

    public function label(): string
    {
        return match ($this) {
            self::TRANSACTIONAL_EMAIL    => 'Email transactionnel',
            self::SALE_ORDER_EMAIL       => 'Email commande de vente',
            self::PRICE_CAMPAIGN_PRODUCT => 'Campagne de prix produits',
        };
    }
}

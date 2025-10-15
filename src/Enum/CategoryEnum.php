<?php

namespace WebEtDesign\MailerBundle\Enum;

enum CategoryEnum: string
{
    case TRANSACTIONAL_EMAIL = 'TRANSACTIONAL_EMAIL';
    case PRICE_CAMPAIGN_PRODUCT = 'PRICE_CAMPAIGN_PRODUCT';
    case SALE_ORDER_EMAIL = 'SALE_ORDER_EMAIL';
    case SALE_ORDER_EMAIL_CANCEL = 'SALE_ORDER_EMAIL_CANCEL';
    case WORK_TICKET = 'WORK_TICKET';
    case CONTRACT_BID = 'CONTRACT_BID';

    public function label(): string
    {
        return match ($this) {
            self::TRANSACTIONAL_EMAIL => 'Email transactionnel',
            self::SALE_ORDER_EMAIL, self::SALE_ORDER_EMAIL_CANCEL => 'Email commande de vente',
            self::PRICE_CAMPAIGN_PRODUCT => 'Campagne de prix produits',
            self::WORK_TICKET => 'Bons de travail',
            self::CONTRACT_BID => 'Offre',
        };
    }
}

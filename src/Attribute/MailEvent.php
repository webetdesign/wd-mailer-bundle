<?php

namespace WebEtDesign\MailerBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class MailEvent
{
    public function __construct(
        public string $name,
        public string $label,
        public int $priority = 0
    ) {}
}

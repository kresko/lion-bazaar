<?php

namespace App\Twig\Components\Footer;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class FooterNavigation
{
    public function getFooterCmsNavigation(): array
    {
        // napravi json

        $test = "{
            \"navigation\": [
                [
                    {\"label\": \"About Us\", \"url\": \"/about\"},
                    {\"label\": \"Contact\", \"url\": \"/contact\"},
                    {\"label\": \"Privacy Policy\", \"url\": \"/privacy\"},
                    {\"label\": \"Terms of Service\", \"url\": \"/terms\"}
                ],
                [
                    {\"label\": \"About Us\", \"url\": \"/about\"},
                    {\"label\": \"Contact\", \"url\": \"/contact\"},
                    {\"label\": \"Privacy Policy\", \"url\": \"/privacy\"},
                    {\"label\": \"Terms of Service\", \"url\": \"/terms\"}
                ]
            ]
        }";

        return json_decode($test, true)['navigation'];
    }
}

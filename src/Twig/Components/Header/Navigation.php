<?php

namespace App\Twig\Components\Header;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use App\Repository\NavigationTreeRepository;

#[AsTwigComponent]
final class Navigation
{
    private NavigationTreeRepository $navigationTreeRepository;

    /**
     * @var string
     */
    private const CHILDREN = 'children';

    public function __construct(NavigationTreeRepository $navigationTreeRepository)
    {
        $this->navigationTreeRepository = $navigationTreeRepository;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getNavigationTree(): ?array
    {
        $navigationTree = $this->navigationTreeRepository->getTreeJson();

        if (!$navigationTree) {
            return null;
        }

        return $navigationTree[0][static::CHILDREN];
    }
}

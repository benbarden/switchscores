<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Twig\Environment;
use Twig\TwigFunction;

use App\Support\Links;

class TwigViewServiceProvider extends ServiceProvider
{
    public function boot(Environment $twig)
    {
        $twig->addFunction(new TwigFunction('eshop_url', function (?string $region, ?string $path) {
            return Links::eshopUrl($region, $path);
        }));

        $twig->addFunction(new TwigFunction('game_url', function ($game) {
            return route('game.show', [
                'id'        => $game->id,
                'linkTitle' => $game->link_title,
            ]);
        }));
    }
}
<?php

namespace App\Domain\GamesCompanySignup;

use App\Models\GamesCompanySignup;

class Director
{
    /**
     * @var Builder
     */
    private $builder;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    public function save()
    {
        $this->builder->getGamesCompanySignup()->save();
    }

    public function getGamesCompanySignup()
    {
        return $this->builder->getGamesCompanySignup();
    }

    public function buildNew($params): void
    {
        $this->buildGamesCompanySignup($params);
    }

    public function buildExisting(GamesCompanySignup $gamesCompanySignup, $params): void
    {
        $this->builder->setGamesCompanySignup($gamesCompanySignup);
        $this->buildGamesCompanySignup($params);
    }

    public function buildGamesCompanySignup($params): void
    {
        if (array_key_exists('contact_name', $params)) {
            $this->builder->setContactName($params['contact_name']);
        }
        if (array_key_exists('contact_role', $params)) {
            $this->builder->setContactRole($params['contact_role']);
        }
        if (array_key_exists('contact_email', $params)) {
            $this->builder->setContactEmail($params['contact_email']);
        }
        if (array_key_exists('existing_company_id', $params)) {
            $this->builder->setExistingCompanyId($params['existing_company_id']);
        }
        if (array_key_exists('new_company_name', $params)) {
            $this->builder->setNewCompanyName($params['new_company_name']);
        }
        if (array_key_exists('new_company_type', $params)) {
            $this->builder->setNewCompanyType($params['new_company_type']);
        }
        if (array_key_exists('new_company_url', $params)) {
            $this->builder->setNewCompanyUrl($params['new_company_url']);
        }
        if (array_key_exists('new_company_twitter', $params)) {
            $this->builder->setNewCompanyTwitter($params['new_company_twitter']);
        }
        if (array_key_exists('list_of_games', $params)) {
            $this->builder->setListOfGames($params['list_of_games']);
        }
    }
}

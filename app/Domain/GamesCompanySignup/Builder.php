<?php

namespace App\Domain\GamesCompanySignup;

use App\Models\GamesCompanySignup;

class Builder
{
    /**
     * @var GamesCompanySignup
     */
    private $gamesCompanySignup;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->gamesCompanySignup = new GamesCompanySignup;
    }

    public function getGamesCompanySignup(): GamesCompanySignup
    {
        return $this->gamesCompanySignup;
    }

    public function setGamesCompanySignup(GamesCompanySignup $gamesCompanySignup): void
    {
        $this->gamesCompanySignup = $gamesCompanySignup;
    }

    public function setContactName($value): Builder
    {
        $this->gamesCompanySignup->contact_name = $value;
        return $this;
    }

    public function setContactRole($value): Builder
    {
        $this->gamesCompanySignup->contact_role = $value;
        return $this;
    }

    public function setContactEmail($value): Builder
    {
        $this->gamesCompanySignup->contact_email = $value;
        return $this;
    }

    public function setExistingCompanyId($value): Builder
    {
        $this->gamesCompanySignup->existing_company_id = $value;
        return $this;
    }

    public function setNewCompanyName($value): Builder
    {
        $this->gamesCompanySignup->new_company_name = $value;
        return $this;
    }

    public function setNewCompanyType($value): Builder
    {
        $this->gamesCompanySignup->new_company_type = $value;
        return $this;
    }

    public function setNewCompanyUrl($value): Builder
    {
        $this->gamesCompanySignup->new_company_url = $value;
        return $this;
    }

    public function setNewCompanyTwitter($value): Builder
    {
        $this->gamesCompanySignup->new_company_twitter = $value;
        return $this;
    }

    public function setListOfGames($value): Builder
    {
        $this->gamesCompanySignup->list_of_games = $value;
        return $this;
    }
}
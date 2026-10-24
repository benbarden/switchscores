<?php

namespace App\Domain\WeeklyBatch;

use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\Url\LinkTitle;
use App\Models\GamesCompany;
use App\Models\WeeklyBatchItem;

/**
 * When a weekly update item is marked LQ, records its publisher as an LQ games company if the
 * publisher isn't in the DB yet (#198). The fetch then confirms that publisher's later games as
 * LQ automatically. Ben chose "always", not an offer: a publisher that doesn't exist has no
 * other games to protect.
 *
 * Publishers that already exist are left alone, LQ or not - the confirm screen's reminder covers
 * those.
 */
class LqPublisherRecorder
{
    const CREATED = 'created';
    const EXISTS = 'exists';
    const LINK_TITLE_TAKEN = 'link_title_taken';
    const NO_PUBLISHER = 'no_publisher';

    public function __construct(
        private GamesCompanyRepository $repoGamesCompany,
        private LinkTitle $linkTitle
    )
    {
    }

    /**
     * Returns one of the outcome constants. Never touches an existing company.
     */
    public function recordFor(WeeklyBatchItem $item): string
    {
        return $this->recordPublisher((string) $item->publisher_normalised)['outcome'];
    }

    /**
     * @return array{outcome: string, company: ?GamesCompany}
     */
    public function recordPublisher(string $name, bool $dryRun = false): array
    {
        $name = trim($name);
        if ($name === '') {
            return ['outcome' => self::NO_PUBLISHER, 'company' => null];
        }

        $existing = $this->repoGamesCompany->findByNameCaseInsensitive($name);
        if ($existing) {
            return ['outcome' => self::EXISTS, 'company' => $existing];
        }

        // Public company pages are looked up by link title, so don't create a second company
        // that would share one (e.g. "Foo Games" and "Foo Games.") - leave it for a person.
        $linkTitle = $this->linkTitle->generate($name);
        $clash = $this->repoGamesCompany->getByLinkTitle($linkTitle);
        if ($clash) {
            return ['outcome' => self::LINK_TITLE_TAKEN, 'company' => $clash];
        }

        if ($dryRun) {
            return ['outcome' => self::CREATED, 'company' => null];
        }

        return ['outcome' => self::CREATED, 'company' => $this->repoGamesCompany->createCompany($name, $linkTitle, true)];
    }
}

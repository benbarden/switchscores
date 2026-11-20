<?php

namespace App\Domain\GamesCompany\Repository;

use App\Models\GamesCompany;
use App\Models\GamesCompanyAlias;
use Illuminate\Support\Collection;

/**
 * Other names a games company appears under, and finding a company by its name or an alias.
 */
class AliasRepository
{
    /**
     * The company with this name, or with this as an alias. Case is ignored. The company's own
     * name wins, so an alias can't hide another company.
     */
    public function findCompanyByNameOrAlias(string $name): ?GamesCompany
    {
        $lower = mb_strtolower(trim($name));
        if ($lower === '') return null;

        $byName = GamesCompany::whereRaw('LOWER(name) = ?', [$lower])->withCount('publisherGames')->first();
        if ($byName) return $byName;

        $alias = GamesCompanyAlias::whereRaw('LOWER(name) = ?', [$lower])->first();

        return $alias
            ? GamesCompany::withCount('publisherGames')->find($alias->games_company_id)
            : null;
    }

    /**
     * Companies whose name starts with the given text, with their publisher game counts.
     * Used to suggest matches for a new publisher name.
     */
    public function getCompaniesStartingWith(string $prefix, int $limit = 5): Collection
    {
        $prefix = trim($prefix);
        if (mb_strlen($prefix) < 2) return collect();

        $like = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $prefix).'%';

        return GamesCompany::where('name', 'LIKE', $like)
            ->withCount('publisherGames')
            ->orderByDesc('publisher_games_count')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function find(int $id): ?GamesCompanyAlias
    {
        return GamesCompanyAlias::find($id);
    }

    public function existsByName(string $name): bool
    {
        return GamesCompanyAlias::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))])->exists();
    }

    public function add(GamesCompany $company, string $name): GamesCompanyAlias
    {
        return GamesCompanyAlias::create(['games_company_id' => $company->id, 'name' => trim($name)]);
    }

    public function delete(GamesCompanyAlias $alias): void
    {
        $alias->delete();
    }
}

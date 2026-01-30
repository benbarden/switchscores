<?php

namespace App\Domain\GameImport;

/**
 * Value object representing a single game from the JSON import file.
 */
class ImportGameData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $releaseDate,
        public readonly ?float $priceGbp,
        public readonly ?string $url,
        public readonly ?string $packshotUrl,
        public readonly ?string $publisher,
        public readonly ?string $players,
        public readonly ?string $category,
        public readonly string $consoleSlug,
        public readonly ?string $sourceFile,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? '',
            releaseDate: $data['release_date'] ?? null,
            priceGbp: isset($data['price_gbp']) ? (float) $data['price_gbp'] : null,
            url: $data['url'] ?? null,
            packshotUrl: $data['packshot_url'] ?? null,
            publisher: $data['publisher'] ?? null,
            players: $data['players'] ?? null,
            category: $data['category'] ?? null,
            consoleSlug: $data['console_slug'] ?? 'switch-1',
            sourceFile: $data['source_file'] ?? null,
        );
    }

    public function toGameParams(int $consoleId, ?int $categoryId, string $linkTitle, ?string $batchDate): array
    {
        $params = [
            'title' => $this->title,
            'link_title' => $linkTitle,
            'console_id' => $consoleId,
            'players' => $this->players,
        ];

        if ($this->releaseDate) {
            $params['eu_release_date'] = $this->releaseDate;
        }

        if ($this->priceGbp !== null) {
            $params['price_eshop'] = number_format($this->priceGbp, 2, '.', '');
        }

        if ($categoryId) {
            $params['category_id'] = $categoryId;
        }

        if ($this->packshotUrl) {
            $params['packshot_square_url_override'] = $this->packshotUrl;
        }

        if ($this->url) {
            $params['nintendo_store_url_override'] = $this->url;
        }

        return $params;
    }
}

<?php

namespace Tests\Feature\Staff;

use App\Models\GscPageSnapshot;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Staff Insights: site pages get their own section at the top, apart from category pages.
 */
class InsightsSitePagesTest extends TestCase
{
    use DatabaseTransactions;

    private function snapshot(string $path, string $type, int $impressions): void
    {
        GscPageSnapshot::create([
            'page_url' => 'https://www.switchscores.com'.$path,
            'page_type' => $type,
            'snapshot_date' => '2031-01-10',
            'window_days' => 28,
            'clicks' => 1,
            'impressions' => $impressions,
            'avg_position' => 5.5,
            'query_count' => 1,
            'top_queries' => [],
        ]);
    }

    public function testSitePagesComeFirstAndApartFromCategories()
    {
        $this->snapshot('/buyers-guide/us', 'site', 900001);
        $this->snapshot('/c/switch-2', 'site', 900000);
        $this->snapshot('/browse/category/rpg-insights-test', 'category', 900002);

        $this->actingAs(new User(['display_name' => 'Insights Test', 'is_staff' => 1]))
            ->get(route('staff.insights.index'))
            ->assertStatus(200)
            ->assertSeeInOrder([
                'Site pages', '/buyers-guide/us', '/c/switch-2',
                'Category pages', '/browse/category/rpg-insights-test',
                'Top rated pages',
            ]);
    }

    public function testSitePagesWithFewerThanTenImpressionsAreLeftOut()
    {
        $this->snapshot('/c/switch-1/2031/01', 'site', 9);
        $this->snapshot('/c/switch-1/2031', 'site', 10);

        $this->actingAs(new User(['display_name' => 'Insights Test', 'is_staff' => 1]))
            ->get(route('staff.insights.index'))
            ->assertStatus(200)
            ->assertSee('/c/switch-1/2031')
            ->assertDontSee('/c/switch-1/2031/01');
    }
}

<?php

namespace Tests\Feature\Console;

use App\Domain\Game\ImageResolver;
use App\Domain\Game\PackshotCacheControl;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * packshots:set-cache-control is a dry run unless --apply is given, and --limit stops it after
 * that many objects changed, so a few can be tried before the full run. The updater is replaced
 * with a stub, so no bucket is touched.
 */
class PackshotsSetCacheControlTest extends TestCase
{
    private function stubUpdater(string $result): object
    {
        $stub = new class(app(ImageResolver::class)) extends PackshotCacheControl {
            public string $result = '';
            public array $applyFlags = [];

            public function keys(): \Generator
            {
                yield 'switch-1/header/1-a.jpg';
                yield 'switch-1/square/1-a.jpg';
                yield 'switch-2/header/2-b.jpg';
            }

            public function update(string $key, bool $apply): string
            {
                $this->applyFlags[] = $apply;
                return $this->result;
            }
        };
        $stub->result = $result;

        $this->app->instance(PackshotCacheControl::class, $stub);

        return $stub;
    }

    public function testDryRunByDefault()
    {
        $stub = $this->stubUpdater(PackshotCacheControl::RESULT_WOULD_UPDATE);

        $this->assertSame(0, Artisan::call('packshots:set-cache-control', ['--pause' => 0]));
        $output = Artisan::output();

        $this->assertStringContainsString('DRY RUN', $output);
        $this->assertStringContainsString('Checked: 3', $output);
        $this->assertSame([false, false, false], $stub->applyFlags);
    }

    public function testApplyPassesThrough()
    {
        $stub = $this->stubUpdater(PackshotCacheControl::RESULT_UPDATED);

        $this->assertSame(0, Artisan::call('packshots:set-cache-control', ['--apply' => true, '--pause' => 0]));
        $output = Artisan::output();

        $this->assertStringContainsString('APPLY', $output);
        $this->assertStringContainsString('Checked: 3', $output);
        $this->assertSame([true, true, true], $stub->applyFlags);
    }

    public function testLimitStopsAfterThatManyChanged()
    {
        $stub = $this->stubUpdater(PackshotCacheControl::RESULT_UPDATED);

        $this->assertSame(0, Artisan::call('packshots:set-cache-control', ['--apply' => true, '--limit' => 2, '--pause' => 0]));
        $output = Artisan::output();

        $this->assertStringContainsString('Stopped at the limit of 2.', $output);
        $this->assertStringContainsString('Checked: 2', $output);
        $this->assertCount(2, $stub->applyFlags);
    }

    public function testObjectsAlreadyDoneDontCountTowardsTheLimit()
    {
        $stub = $this->stubUpdater(PackshotCacheControl::RESULT_ALREADY_SET);

        $this->assertSame(0, Artisan::call('packshots:set-cache-control', ['--apply' => true, '--limit' => 1, '--pause' => 0]));

        $this->assertStringContainsString('Checked: 3', Artisan::output());
        $this->assertCount(3, $stub->applyFlags);
    }

    public function testMissingObjectsAreListed()
    {
        $this->stubUpdater(PackshotCacheControl::RESULT_MISSING);

        $this->assertSame(0, Artisan::call('packshots:set-cache-control', ['--pause' => 0]));
        $output = Artisan::output();

        $this->assertStringContainsString('Missing (first 20):', $output);
        $this->assertStringContainsString('switch-2/header/2-b.jpg', $output);
    }
}

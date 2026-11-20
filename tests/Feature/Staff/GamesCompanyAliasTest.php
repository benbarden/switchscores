<?php

namespace Tests\Feature\Staff;

use App\Domain\GamesCompany\Repository\AliasRepository;
use App\Models\GamesCompany;
use App\Models\GamesCompanyAlias;
use App\Models\User;
use App\Models\WeeklyBatch;
use App\Models\WeeklyBatchItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Games company aliases, and linking a new publisher to an existing company on the weekly
 * update's Publishers step (e.g. "Natsume Inc." to Natsume).
 */
class GamesCompanyAliasTest extends TestCase
{
    use DatabaseTransactions;

    private WeeklyBatch $batch;

    public function setUp(): void
    {
        parent::setUp();
        // batch_date is unique; a far-future date can't clash with a real batch
        $this->batch = WeeklyBatch::create([
            'batch_date' => '2095-04-'.str_pad((string) random_int(1, 28), 2, '0', STR_PAD_LEFT),
            'status' => WeeklyBatch::STATUS_SETUP,
        ]);
    }

    private function owner(): User
    {
        return new User([
            'display_name' => 'Games Company Alias Test',
            'email' => 'xx.company.alias.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function company(string $name, bool $isLq = false): GamesCompany
    {
        $company = new GamesCompany();
        $company->name = $name;
        $company->link_title = 'test-alias-'.uniqid();
        $company->is_low_quality = $isLq ? 1 : 0;
        $company->save();
        return $company;
    }

    private function item(string $publisher): WeeklyBatchItem
    {
        return WeeklyBatchItem::create([
            'batch_id' => $this->batch->id,
            'console' => 'switch-2',
            'list_type' => 'new',
            'page_number' => 1,
            'sort_order' => 0,
            'title' => 'Zqx Alias Game '.uniqid(),
            'title_raw' => 'Zqx Alias Game',
            'publisher_normalised' => $publisher,
            'item_status' => WeeklyBatchItem::STATUS_CATEGORY_PENDING,
        ]);
    }

    private function listUrl(string $name): string
    {
        return route('staff.games.weekly-updates.list.'.$name, [
            'batchId' => $this->batch->id, 'console' => 'switch-2', 'listType' => 'new',
        ]);
    }

    private function repo(): AliasRepository
    {
        return app(AliasRepository::class);
    }

    public function testAnAliasFindsItsCompanyIgnoringCase()
    {
        $company = $this->company('Zqxsume');
        $this->repo()->add($company, 'Zqxsume Inc.');

        $this->assertSame($company->id, $this->repo()->findCompanyByNameOrAlias('ZQXSUME INC.')->id);
        $this->assertSame($company->id, $this->repo()->findCompanyByNameOrAlias('zqxsume')->id);
        $this->assertNull($this->repo()->findCompanyByNameOrAlias('Zqxsume Ltd'));
    }

    public function testACompanyNameWinsOverAnotherCompanysAlias()
    {
        $own = $this->company('Zqx Name Wins');
        $other = $this->company('Zqx Other Company');
        GamesCompanyAlias::create(['games_company_id' => $other->id, 'name' => 'Zqx Name Wins Ltd']);
        $this->repo()->add($other, 'Zqx Name Wins Two');

        $this->assertSame($own->id, $this->repo()->findCompanyByNameOrAlias('Zqx Name Wins')->id);
    }

    public function testPossibleMatchesStartWithTheFirstWord()
    {
        $company = $this->company('Zqxsume');

        $this->item('Zqxsume Inc.');
        $response = $this->actingAs($this->owner())->get($this->listUrl('publishers'));

        $response->assertStatus(200);
        $response->assertSee("Zqxsume (0 games, id: {$company->id})");
        $response->assertSee('Save as New (LQ)');
    }

    public function testLinkAndAddAliasRenamesTheBatchAndSavesTheAlias()
    {
        $company = $this->company('Zqxsume');
        $item = $this->item('Zqxsume Inc.');

        $response = $this->actingAs($this->owner())->post($this->listUrl('publishers.link'), [
            'old_name' => 'Zqxsume Inc.', 'company_id' => $company->id, 'add_alias' => '1',
        ]);

        $response->assertJson(['status' => 'linked', 'company_id' => $company->id, 'name' => 'Zqxsume', 'alias_note' => 'Alias "Zqxsume Inc." added.']);
        $this->assertSame('Zqxsume', $item->fresh()->publisher_normalised);
        $this->assertSame($company->id, $this->repo()->findCompanyByNameOrAlias('Zqxsume Inc.')->id);
    }

    public function testLinkWithoutAliasSavesNoAlias()
    {
        $company = $this->company('Zqxsume');
        $this->item('Zqxsume Inc.');

        $this->actingAs($this->owner())->post($this->listUrl('publishers.link'), [
            'old_name' => 'Zqxsume Inc.', 'company_id' => $company->id,
        ])->assertJson(['status' => 'linked', 'alias_note' => null]);

        $this->assertNull($this->repo()->findCompanyByNameOrAlias('Zqxsume Inc.'));
    }

    public function testAnAliasOwnedByAnotherCompanyIsNotMoved()
    {
        $owner = $this->company('Zqx Alias Owner');
        $this->repo()->add($owner, 'Zqx Shared Name');
        $other = $this->company('Zqx Alias Other');
        $this->item('Zqx Shared Name');

        $response = $this->actingAs($this->owner())->post($this->listUrl('publishers.link'), [
            'old_name' => 'Zqx Shared Name', 'company_id' => $other->id, 'add_alias' => '1',
        ]);

        $response->assertJsonPath('alias_note', "Alias not added: \"Zqx Shared Name\" already belongs to Zqx Alias Owner (id: {$owner->id}).");
        $this->assertSame($owner->id, $this->repo()->findCompanyByNameOrAlias('Zqx Shared Name')->id);
    }

    public function testSaveAsNewOnAnExistingNameLinksInsteadOfCreating()
    {
        $company = $this->company('Zqxsume');
        $item = $this->item('Zqxsume Inc.');
        $this->repo()->add($company, 'Zqxsume Inc.');
        $before = GamesCompany::count();

        $response = $this->actingAs($this->owner())->post($this->listUrl('publishers.create'), [
            'old_name' => 'Zqxsume Inc.', 'new_name' => 'Zqxsume Inc.',
        ]);

        $response->assertJson(['status' => 'linked', 'company_id' => $company->id]);
        $this->assertSame($before, GamesCompany::count());
        $this->assertSame('Zqxsume', $item->fresh()->publisher_normalised);
    }

    public function testLinkingToAnLqCompanyMarksTheBatchGamesLq()
    {
        $company = $this->company('Zqx LQ Publisher', true);
        $item = $this->item('Zqx LQ Publisher Ltd');

        $this->actingAs($this->owner())->post($this->listUrl('publishers.link'), [
            'old_name' => 'Zqx LQ Publisher Ltd', 'company_id' => $company->id,
        ])->assertJson(['status' => 'linked', 'is_lq' => true, 'item_count' => 1]);

        $this->assertSame(WeeklyBatchItem::STATUS_LOW_QUALITY, $item->fresh()->item_status);
    }

    public function testCompanyPageListsAndRemovesAliases()
    {
        $company = $this->company('Zqxsume');
        $alias = $this->repo()->add($company, 'Zqxsume Inc.');

        $this->actingAs($this->owner())->get(route('staff.games-companies.show', ['gamesCompany' => $company->id]))
            ->assertStatus(200)
            ->assertSee('Zqxsume Inc.');

        $this->actingAs($this->owner())
            ->post(route('staff.games-companies.alias.remove', ['gamesCompany' => $company->id, 'aliasId' => $alias->id]))
            ->assertRedirect(route('staff.games-companies.show', ['gamesCompany' => $company->id]));

        $this->assertNull(GamesCompanyAlias::find($alias->id));
    }
}

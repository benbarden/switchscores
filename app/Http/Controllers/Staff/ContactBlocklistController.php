<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;

use App\Domain\Contact\Repository as ContactRepository;
use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;
use App\Enums\ContactBlockType;

class ContactBlocklistController extends Controller
{
    use ValidatesRequests;

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private ContactRepository $repoContact
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Contact blocklist';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::contactSubpage($pageTitle))->bindings;

        $bindings['BlocklistEntries'] = $this->repoContact->getAllBlocklist();
        $bindings['BlockTypeOptions'] = ContactBlockType::options();

        return view('staff.contact.blocklist', $bindings);
    }

    public function add(Request $request)
    {
        $this->validate($request, [
            'value' => 'required|max:150',
            'type' => 'required|in:email,domain',
            'note' => 'nullable|max:1000',
        ]);

        $this->repoContact->addToBlocklist(
            $request->post('value'),
            $request->post('type'),
            $request->post('note')
        );

        return redirect(route('staff.contact.blocklist'));
    }

    public function delete($entryId)
    {
        $entry = $this->repoContact->findBlocklistEntry($entryId);
        if (!$entry) abort(404);

        $this->repoContact->removeFromBlocklist($entry);

        return redirect(route('staff.contact.blocklist'));
    }
}

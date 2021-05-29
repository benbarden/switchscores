<?php

namespace App\Http\Controllers\Owner;

use Illuminate\Routing\Controller as Controller;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use App\Traits\AuthUser;
use App\Traits\SwitchServices;
use App\Traits\StaffView;

use App\Domain\InviteCode\Repository as InviteCodeRepository;
use App\Domain\InviteCode\CodeGenerator;

class InviteCodeController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use StaffView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'invite_code' => 'required',
    ];

    private $validationRulesGenerator = [
        'codes_count' => 'required|numeric|min:1|max:10',
    ];

    protected $repoInviteCode;

    public function __construct(
        InviteCodeRepository $repoInviteCode
    )
    {
        $this->repoInviteCode = $repoInviteCode;
    }

    public function showList()
    {
        $bindings = $this->getBindingsDashboardGenericSubpage('Invite codes');

        $bindings['InviteCodeList'] = $this->repoInviteCode->getAll();

        return view('owner.invite-code.list', $bindings);
    }

    public function generateCodes()
    {
        $bindings = $this->getBindingsDashboardGenericSubpage('Generate invite codes');

        $request = request();

        $generator = new CodeGenerator();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRulesGenerator);

            $codesCount = $request->codes_count;

            for ($i=1; $i<=$codesCount; $i++) {

                $timesUsed = 0;
                $timesLeft = 1;
                $isActive = 1;
                //$inviteCode = 'ABC'.$i;
                $inviteCode = $generator->generate();

                $validator = Validator::make($request->all(), $this->validationRulesGenerator);
                try {
                    $this->repoInviteCode->create($inviteCode, $timesUsed, $timesLeft, $isActive);
                } catch (\Exception $e) {
                    $validator->errors()->add('codes_count', 'Failed - duplicate code!');
                    return redirect(route('owner.invite-code.generate-codes'))
                        ->withErrors($validator)
                        ->withInput();
                }

            }

            return redirect(route('owner.invite-code.list'));

        }

        return view('owner.invite-code.generate-codes', $bindings);
    }

    public function addInviteCode()
    {
        $bindings = $this->getBindingsDashboardGenericSubpage('Add invite code');

        $request = request();

        if ($request->isMethod('post')) {

            //$this->validate($request, $this->validationRules);

            $validator = Validator::make($request->all(), $this->validationRules);

            if ($validator->fails()) {
                return redirect(route('owner.invite-code.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            $existingRecord = $this->repoInviteCode->getByCode($request->invite_code);

            $validator->after(function ($validator) use ($existingRecord) {
                // Check for duplicates
                if ($existingRecord != null) {
                    $validator->errors()->add('title', 'Code already exists!');
                }
            });

            if ($validator->fails()) {
                return redirect(route('owner.invite-code.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // All ok
            $timesUsed = $request->times_used;
            $timesLeft = $request->times_left;
            if (!$timesUsed) {
                $timesUsed = 0;
            }
            if (!$timesLeft) {
                $timesLeft = 0;
            }
            $isActive = $request->is_active == 'on' ? 1 : 0;
            $this->repoInviteCode->create($request->invite_code, $timesUsed, $timesLeft, $isActive);

            return redirect(route('owner.invite-code.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['FormMode'] = 'add';

        return view('owner.invite-code.add', $bindings);
    }

    public function editInviteCode($inviteCodeId)
    {
        $bindings = $this->getBindingsDashboardGenericSubpage('Edit invite code');

        $inviteCodeData = $this->repoInviteCode->find($inviteCodeId);
        if (!$inviteCodeData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $timesUsed = $request->times_used;
            $timesLeft = $request->times_left;
            if (!$timesUsed) {
                $timesUsed = 0;
            }
            if (!$timesLeft) {
                $timesLeft = 0;
            }
            $isActive = $request->is_active == 'on' ? 1 : 0;
            $this->repoInviteCode->edit($inviteCodeData, $request->invite_code, $timesUsed, $timesLeft, $isActive);

            return redirect(route('owner.invite-code.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['InviteCodeData'] = $inviteCodeData;
        $bindings['InviteCodeId'] = $inviteCodeId;

        return view('owner.invite-code.edit', $bindings);
    }

    public function deleteInviteCode($inviteCodeId)
    {
        $bindings = $this->getBindingsDashboardGenericSubpage('Delete invite code');

        $inviteCodeData = $this->repoInviteCode->find($inviteCodeId);
        if (!$inviteCodeData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $this->repoInviteCode->delete($inviteCodeId);

            // Done

            return redirect(route('owner.invite-code.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['InviteCodeData'] = $inviteCodeData;
        $bindings['InviteCodeId'] = $inviteCodeId;

        return view('owner.invite-code.delete', $bindings);
    }
}
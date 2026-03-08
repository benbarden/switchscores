<?php

namespace App\Http\Controllers\Members;

use App\Domain\User\VerifyEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('throttle:5,60')->only('sendVerification');
    }

    public function sendVerification(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return redirect()->back()->with('success', 'Your email is already verified.');
        }

        $verificationUrl = URL::temporarySignedRoute(
            'members.email.verify',
            now()->addHours(24),
            ['id' => $user->id]
        );

        Mail::to($user->email)->send(new VerifyEmail($verificationUrl));

        $user->verification_email_sent_at = now();
        $user->save();

        return redirect()->back()->with('success', 'Verification email sent! Check your inbox.');
    }

    public function verify(Request $request, $id)
    {
        if (!$request->hasValidSignature()) {
            return redirect()->route('members.index')
                ->with('error', 'This verification link has expired or is invalid.');
        }

        $user = User::findOrFail($id);

        if ($user->email_verified_at) {
            return redirect()->route('members.index')
                ->with('success', 'Your email is already verified.');
        }

        $user->email_verified_at = now();
        $user->save();

        return redirect()->route('members.index')
            ->with('success', 'Your email has been verified! You now have full access.');
    }
}

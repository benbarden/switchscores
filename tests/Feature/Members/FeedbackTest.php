<?php

namespace Tests\Feature\Members;

use App\Domain\Contact\ContactNotification;
use App\Enums\ContactRequestType;
use App\Models\ContactSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The member feedback form: verified members send feedback to the site's contact mailbox
 * without the site showing an email address.
 */
class FeedbackTest extends TestCase
{
    use DatabaseTransactions;

    const NOTIFY_ADDRESS = 'site-contact.feedback.test@example.com';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        config(['services.contact.notify_address' => self::NOTIFY_ADDRESS]);
    }

    private function member(bool $verified = true): User
    {
        $user = new User([
            'display_name' => 'Feedback Test',
            'email' => 'xx.feedback.test.'.uniqid().'@switchscores.com',
        ]);
        $user->email_verified_at = $verified ? now() : null;
        $user->save();

        return $user;
    }

    private function postFeedback(User $member, array $data = [])
    {
        return $this->actingAs($member)->post(route('members.feedback.submit'), array_merge([
            'request_type' => ContactRequestType::MEMBER_FEEDBACK_REVIEWERS->value,
            'message' => 'The reviewers dashboard is great, but could it show more stats?',
        ], $data));
    }

    public function testGuestIsSentToLogin()
    {
        $this->get(route('members.feedback.show'))->assertRedirect(route('login'));
    }

    public function testUnverifiedMemberCannotUseIt()
    {
        $member = $this->member(false);

        $this->actingAs($member)->get(route('members.feedback.show'))->assertRedirect();
        $this->postFeedback($member)->assertRedirect();

        $this->assertSame(0, ContactSubmission::where('email', $member->email)->count());
        Mail::assertNothingSent();
    }

    public function testFormPicksTheDashboardItWasLinkedFrom()
    {
        $this->actingAs($this->member())
            ->get(route('members.feedback.show', ['from' => 'reviewers']))
            ->assertStatus(200)
            ->assertSee('value="member_feedback_reviewers" selected', false)
            ->assertDontSee('@switchscores.com');
    }

    public function testFeedbackIsStoredAndEmailedWithReplyToTheMember()
    {
        $member = $this->member();

        $this->postFeedback($member)
            ->assertRedirect(route('members.feedback.show').'?msg=sent');

        $submission = ContactSubmission::where('email', $member->email)->firstOrFail();
        $this->assertSame($member->id, (int) $submission->user_id);
        $this->assertSame('Feedback Test', $submission->name);
        $this->assertSame(ContactRequestType::MEMBER_FEEDBACK_REVIEWERS->value, $submission->request_type);
        $this->assertSame('new', $submission->status);

        Mail::assertSent(ContactNotification::class, function (ContactNotification $mail) use ($member) {
            return $mail->hasTo(self::NOTIFY_ADDRESS)
                && $mail->hasReplyTo($member->email)
                && $mail->hasSubject('Switch Scores member feedback: Reviewers dashboard');
        });
    }

    public function testHoneypotStoresAndSendsNothing()
    {
        $member = $this->member();

        $this->postFeedback($member, ['website' => 'http://spam.example.com'])
            ->assertRedirect(route('members.feedback.show').'?msg=sent');

        $this->assertSame(0, ContactSubmission::where('email', $member->email)->count());
        Mail::assertNothingSent();
    }

    public function testPublicContactReasonsAreRejected()
    {
        $member = $this->member();

        $this->postFeedback($member, ['request_type' => ContactRequestType::OTHER->value])
            ->assertSessionHasErrors('request_type');

        $this->assertSame(0, ContactSubmission::where('email', $member->email)->count());
    }

    public function testSendingIsRateLimited()
    {
        $member = $this->member();

        for ($i = 0; $i < 5; $i++) {
            $this->postFeedback($member)->assertRedirect();
        }

        $this->postFeedback($member)->assertStatus(429);
        $this->assertSame(5, ContactSubmission::where('email', $member->email)->count());
    }

    public function testDashboardLinksGoToTheFormNotAnEmailAddress()
    {
        $this->actingAs($this->member())
            ->get(route('members.index'))
            ->assertSee(route('members.feedback.show', ['from' => 'members']), false)
            ->assertDontSee('mailto:ben@', false);
    }
}

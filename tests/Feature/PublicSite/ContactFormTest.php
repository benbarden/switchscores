<?php

namespace Tests\Feature\PublicSite;

use App\Domain\Contact\ContactNotification;
use App\Domain\Contact\Repository as ContactRepository;
use App\Enums\ContactBlockType;
use App\Models\ContactSubmission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The public contact form, and the pages that point people to it instead of an email address.
 */
class ContactFormTest extends TestCase
{
    use DatabaseTransactions;

    const NOTIFY_ADDRESS = 'site-contact.form.test@example.com';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        config([
            'services.contact.notify_address' => self::NOTIFY_ADDRESS,
            // No secret means the Turnstile check is skipped.
            'services.turnstile.secret' => null,
        ]);
    }

    private function postContact(string $email)
    {
        return $this->post(route('contact.show'), [
            'name' => 'Contact Form Test',
            'email' => $email,
            'request_type' => 'correction',
            'message' => 'The price shown for this game is out of date.',
        ]);
    }

    public function testMessageIsStoredAndEmailedToTheContactMailbox()
    {
        $email = 'xx.contact.form.'.uniqid().'@example.com';

        $this->postContact($email)->assertRedirect(route('contact.success'));

        $submission = ContactSubmission::where('email', $email)->firstOrFail();
        $this->assertNull($submission->user_id);
        $this->assertSame('new', $submission->status);

        Mail::assertSent(ContactNotification::class, function (ContactNotification $mail) use ($email) {
            return $mail->hasTo(self::NOTIFY_ADDRESS)
                && $mail->hasReplyTo($email)
                && $mail->hasSubject('Switch Scores contact form: Correction / data fix (wrong price, wrong info, missing game)');
        });
    }

    public function testBlockedSenderIsStoredButNotEmailed()
    {
        $email = 'xx.contact.blocked.'.uniqid().'@example.com';
        app(ContactRepository::class)->addToBlocklist($email, ContactBlockType::EMAIL->value);

        $this->postContact($email)->assertRedirect(route('contact.success'));

        $this->assertSame('blocked', ContactSubmission::where('email', $email)->firstOrFail()->status);
        Mail::assertNothingSent();
    }

    public function testMessageIsKeptWhenNoContactMailboxIsSet()
    {
        config(['services.contact.notify_address' => null]);
        $email = 'xx.contact.unset.'.uniqid().'@example.com';

        $this->postContact($email)->assertRedirect(route('contact.success'));

        $this->assertSame(1, ContactSubmission::where('email', $email)->count());
        Mail::assertNothingSent();
    }

    public function testReviewSitesPartnerPageLinksToTheContactForm()
    {
        $this->get(route('partners.review-sites.landing'))
            ->assertStatus(200)
            ->assertSee(route('contact.show'), false)
            ->assertDontSee('mailto:', false);
    }

    public function testPrivacyPageLinksToTheContactForm()
    {
        $this->get(route('privacy'))
            ->assertStatus(200)
            ->assertSee(route('contact.show'), false)
            ->assertDontSee('support [at]', false);
    }
}

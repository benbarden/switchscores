<?php

namespace App\Enums;

enum ContactRequestType: string
{
    case CORRECTION   = 'correction';
    case PARTNERSHIP  = 'partnership';
    case BUSINESS     = 'business';
    case OTHER        = 'other';

    // Sent from the member feedback form, one per dashboard it was sent from.
    case MEMBER_FEEDBACK_MEMBERS         = 'member_feedback_members';
    case MEMBER_FEEDBACK_REVIEWERS       = 'member_feedback_reviewers';
    case MEMBER_FEEDBACK_GAMES_COMPANIES = 'member_feedback_games_companies';

    /**
     * The reasons offered on the public contact form.
     */
    public static function options(): array
    {
        return [
            self::CORRECTION->value  => 'Correction / data fix (wrong price, wrong info, missing game)',
            self::PARTNERSHIP->value => 'Review site partnership / feed',
            self::BUSINESS->value    => 'Business / advertising / affiliate',
            self::OTHER->value       => 'Something else',
        ];
    }

    /**
     * The dashboards offered on the member feedback form.
     */
    public static function memberFeedbackOptions(): array
    {
        return [
            self::MEMBER_FEEDBACK_MEMBERS->value         => 'Members dashboard',
            self::MEMBER_FEEDBACK_REVIEWERS->value       => 'Reviewers dashboard',
            self::MEMBER_FEEDBACK_GAMES_COMPANIES->value => 'Games companies dashboard',
        ];
    }

    /**
     * Labels for every type, for the staff contact pages and notification emails.
     */
    public static function allLabels(): array
    {
        $labels = self::options();
        foreach (self::memberFeedbackOptions() as $value => $label) {
            $labels[$value] = 'Member feedback: '.$label;
        }

        return $labels;
    }

    /**
     * Maps the ?from= value on a feedback link to the dashboard it came from.
     */
    public static function fromMemberFeedbackArea(?string $area): self
    {
        return match ($area) {
            'reviewers' => self::MEMBER_FEEDBACK_REVIEWERS,
            'games-companies' => self::MEMBER_FEEDBACK_GAMES_COMPANIES,
            default => self::MEMBER_FEEDBACK_MEMBERS,
        };
    }

    public function isMemberFeedback(): bool
    {
        return array_key_exists($this->value, self::memberFeedbackOptions());
    }

    public function label(): string
    {
        return self::allLabels()[$this->value];
    }
}

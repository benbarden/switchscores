<?php

namespace Tests\Unit\Domain\InviteCodeRequest;

use App\Models\InviteCodeRequest;
use Tests\TestCase;

use App\Domain\InviteCodeRequest\SpamScore;

class SpamScoreTest extends TestCase
{
    public function testZeroScore()
    {
        $spamScore = new SpamScore();

        $expected = 0;
        $actual = $spamScore->getScore();

        $this->assertEquals($expected, $actual);
    }

    public function testExistingTimesRequested1()
    {
        $data = [
            'waitlist_email' => 'mr.test.user@gmail.com',
            'waitlist_bio' => 'ABCDEFG',
            'times_requested' => 1,
            'status' => InviteCodeRequest::STATUS_PENDING,
        ];
        $request = new InviteCodeRequest($data);

        $spamScore = new SpamScore($request);
        $spamScore->updateScoreTimesRequested();

        $expected = 1;
        $actual = $spamScore->getScore();

        $this->assertEquals($expected, $actual);
    }

    public function testExistingTimesRequested4()
    {
        $data = [
            'waitlist_email' => 'mr.test.user@gmail.com',
            'waitlist_bio' => 'ABCDEFG',
            'times_requested' => 4,
            'status' => InviteCodeRequest::STATUS_PENDING,
        ];
        $request = new InviteCodeRequest($data);

        $spamScore = new SpamScore($request);
        $spamScore->updateScoreTimesRequested();

        $expected = 4;
        $actual = $spamScore->getScore();

        $this->assertEquals($expected, $actual);
    }

    public function testNewRequestBioNoSpaces()
    {
        $data = [
            'waitlist_email' => 'mr.test.user@gmail.com',
            'waitlist_bio' => 'ABCDEFG',
            'times_requested' => 0,
            'status' => InviteCodeRequest::STATUS_PENDING,
        ];
        $request = new InviteCodeRequest($data);

        $spamScore = new SpamScore($request);
        $spamScore->updateScoreBio();

        $expected = 2;
        $actual = $spamScore->getScore();

        $this->assertEquals($expected, $actual);
    }

    public function testNewRequestBioNoSpacesNotGoogle()
    {
        $data = [
            'waitlist_email' => 'mr.test.user@gmail.com',
            'waitlist_bio' => 'google',
            'times_requested' => 0,
            'status' => InviteCodeRequest::STATUS_PENDING,
        ];
        $request = new InviteCodeRequest($data);

        $spamScore = new SpamScore($request);
        $spamScore->updateScoreBio();

        $expected = 0;
        $actual = $spamScore->getScore();

        $this->assertEquals($expected, $actual);
    }

    public function testAllOptions()
    {
        $data = [
            'waitlist_email' => 'mr.test.user@gmail.com',
            'waitlist_bio' => 'ABCDEFG',
            'times_requested' => 4,
            'status' => InviteCodeRequest::STATUS_PENDING,
        ];
        $request = new InviteCodeRequest($data);

        $spamScore = new SpamScore($request);
        $spamScore->updateAll();

        $expected = 6;
        $actual = $spamScore->getScore();

        $this->assertEquals($expected, $actual);
    }


}

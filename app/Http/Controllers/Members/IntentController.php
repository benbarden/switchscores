<?php

namespace App\Http\Controllers\Members;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\UserWishlist\Repository as UserWishlistRepository;
use App\Domain\View\Breadcrumbs\MembersBreadcrumbs;
use App\Domain\View\PageBuilders\MembersPageBuilder;
use App\Enums\MemberIntent;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class IntentController extends Controller
{
    private const SESSION_KEY = 'member_intent';

    public function __construct(
        private MembersPageBuilder $pageBuilder,
        private GameRepository $repoGame,
        private UserWishlistRepository $repoWishlist
    ) {
        $this->middleware('auth');
    }

    /**
     * Handle an intent action.
     * If user is verified, execute immediately. Otherwise, store and prompt for verification.
     */
    public function handle(Request $request, string $action, int $gameId)
    {
        $intent = MemberIntent::tryFrom($action);
        if (!$intent) {
            return redirect()->route('members.index')
                ->with('error', 'Invalid action.');
        }

        $game = $this->repoGame->find($gameId);
        if (!$game) {
            return redirect()->route('members.index')
                ->with('error', 'Game not found.');
        }

        $user = $request->user();

        // If verified, execute immediately
        if ($user->isEmailVerified()) {
            return $this->executeIntent($intent, $gameId, $user);
        }

        // Store intent in session and show verification prompt
        $request->session()->put(self::SESSION_KEY, [
            'action' => $action,
            'game_id' => $gameId,
            'game_title' => $game->title,
        ]);

        return redirect()->route('members.intent.verify-prompt');
    }

    /**
     * Show the verification prompt page.
     */
    public function verifyPrompt(Request $request)
    {
        $intentData = $request->session()->get(self::SESSION_KEY);

        $pageTitle = 'Verify your email';
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::membersGenericTopLevel($pageTitle))->bindings;

        $bindings['IntentData'] = $intentData;
        $bindings['UserData'] = $request->user();

        if ($intentData) {
            $intent = MemberIntent::tryFrom($intentData['action']);
            $bindings['IntentLabel'] = $intent?->label() ?? 'complete this action';
        }

        return view('members.intent.verify-prompt', $bindings);
    }

    /**
     * Get intent data from session (for embedding in verification URL).
     */
    public static function getIntentFromSession(Request $request): ?array
    {
        return $request->session()->get(self::SESSION_KEY);
    }

    /**
     * Clear intent from session.
     */
    public static function clearIntentFromSession(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    /**
     * Execute any pending intent after verification.
     * Called from EmailVerificationController after successful verification.
     */
    public static function executePendingIntent(Request $request, $user): ?string
    {
        $intentData = $request->session()->get(self::SESSION_KEY);
        if (!$intentData) {
            return null;
        }

        $intent = MemberIntent::tryFrom($intentData['action']);
        if (!$intent) {
            $request->session()->forget(self::SESSION_KEY);
            return null;
        }

        $gameId = $intentData['game_id'];

        // Clear the intent from session
        $request->session()->forget(self::SESSION_KEY);

        // Return redirect URL based on intent
        return match($intent) {
            MemberIntent::WISHLIST_ADD => route('members.intent.execute', ['action' => $intent->value, 'gameId' => $gameId]),
            MemberIntent::COLLECTION_ADD => route('members.collection.add', ['gameId' => $gameId]),
            MemberIntent::QUICK_REVIEW => route('members.quick-reviews.add', ['gameId' => $gameId]),
        };
    }

    /**
     * Execute an intent directly (for verified users or after verification).
     */
    public function execute(Request $request, string $action, int $gameId)
    {
        $user = $request->user();

        if (!$user->isEmailVerified()) {
            return redirect()->route('members.intent.verify-prompt')
                ->with('error', 'Please verify your email first.');
        }

        $intent = MemberIntent::tryFrom($action);
        if (!$intent) {
            return redirect()->route('members.index')
                ->with('error', 'Invalid action.');
        }

        return $this->executeIntent($intent, $gameId, $user);
    }

    /**
     * Execute the intent action.
     */
    private function executeIntent(MemberIntent $intent, int $gameId, $user)
    {
        return match($intent) {
            MemberIntent::WISHLIST_ADD => $this->executeWishlistAdd($gameId, $user),
            MemberIntent::COLLECTION_ADD => redirect()->route('members.collection.add', ['gameId' => $gameId]),
            MemberIntent::QUICK_REVIEW => redirect()->route('members.quick-reviews.add', ['gameId' => $gameId]),
        };
    }

    /**
     * Add game to wishlist.
     */
    private function executeWishlistAdd(int $gameId, $user)
    {
        $game = $this->repoGame->find($gameId);
        if (!$game) {
            return redirect()->route('members.index')
                ->with('error', 'Game not found.');
        }

        // Check if already in wishlist
        if ($this->repoWishlist->isGameInWishlist($user->id, $gameId)) {
            return redirect()->route('members.wishlist.index')
                ->with('success', $game->title . ' is already in your wishlist.');
        }

        $this->repoWishlist->add($user->id, $gameId);

        // Check if this was after verification (no prior session means they just verified)
        $message = $game->title . ' has been added to your wishlist!';

        return redirect()->route('members.wishlist.index')
            ->with('success', $message);
    }
}

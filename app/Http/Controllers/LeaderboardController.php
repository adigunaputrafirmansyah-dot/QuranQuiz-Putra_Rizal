<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Services\LeaderboardService;

class LeaderboardController extends Controller
{
    public function __construct(
        private LeaderboardService $leaderboard
    ) {
    }

    public function show(Quiz $quiz)
    {
        $top = $this->leaderboard->getTopForQuiz($quiz, 20);
        $myRank = auth()->check()
            ? $this->leaderboard->getUserRank($quiz, auth()->id())
            : null;

        return view('quiz.leaderboard', [
            'quiz' => $quiz,
            'top' => $top,
            'myRank' => $myRank,
        ]);
    }

    public function global()
    {
        $top = $this->leaderboard->getGlobalTop(20);

        return view('quiz.leaderboard-global', compact('top'));
    }
}

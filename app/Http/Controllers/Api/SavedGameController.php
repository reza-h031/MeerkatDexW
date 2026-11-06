<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedGameController extends Controller
{
    /**
     * Get all saved games and their count.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $games = $user->savedGames()
            ->with([
                'developer',
                'publisher',
                'platforms',
                'genres',
                'images',
                'ratings',
            ])
            ->get();

        return response()->json([
            'count' => $games->count(),
            'games' => GameResource::collection($games)
                ->resolve($request),
        ]);
    }

    /**
     * Check whether a game is saved by the current user.
     */
    public function check(Request $request, Game $game): JsonResponse
    {
        $isSaved = $request->user()
            ->savedGames()
            ->where('games.id', $game->id)
            ->exists();

        return response()->json([
            'game_id' => $game->id,
            'is_saved' => $isSaved,
            'result' => $isSaved,
        ]);
    }

    /**
     * Add a game to the current user's saved list.
     */
    public function store(Request $request, Game $game): JsonResponse
    {
        $user = $request->user();

        $alreadySaved = $user->savedGames()
            ->where('games.id', $game->id)
            ->exists();

        if (!$alreadySaved) {
            $user->savedGames()->attach($game->id);
        }

        return response()->json([
            'message' => $alreadySaved
                ? 'Game already saved'
                : 'Game added to saved list',
            'game_id' => $game->id,
            'is_saved' => true,
            'added' => !$alreadySaved,
        ], $alreadySaved ? 200 : 201);
    }

    /**
     * Remove a game from the current user's saved list.
     */
    public function destroy(Request $request, Game $game): JsonResponse
    {
        $removed = $request->user()
            ->savedGames()
            ->detach($game->id) > 0;

        return response()->json([
            'message' => $removed
                ? 'Game removed from saved list'
                : 'Game was not in saved list',
            'game_id' => $game->id,
            'is_saved' => false,
            'removed' => $removed,
        ]);
    }
}

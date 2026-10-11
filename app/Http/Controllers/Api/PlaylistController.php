<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Playlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaylistResource;
use App\Models\Game;
use App\Models\Playlist;
use App\Models\Rating;
use Illuminate\Database\Eloquent\Builder;

class PlaylistController extends Controller
{
    private const DYNAMIC_PLAYLIST_LIMIT = 10;

    public function index()
    {
        $playlists = Playlist::query()
            ->with([
                'games.ratings',
                'games.genres',
                'games.platforms',
                'games.images',
            ])
            ->orderBy('id')
            ->get();

        foreach ($playlists as $playlist) {
            $games = match ($playlist->name) {
                'Recently Added' => $this->getRecentlyAddedGames(),

                'Top Rated' => $this->getTopRatedGames(),

                default => null,
            };

            /*
             * Only dynamic playlists receive a replacement collection.
             * All other playlists keep their JSON-defined game relations.
             */
            if ($games !== null) {
                $playlist->setRelation('games', $games);
            }
        }

        return PlaylistResource::collection($playlists);
    }

    public function show(Playlist $playlist)
    {
        $playlist->load([
            'games.ratings',
            'games.genres',
            'games.platforms',
            'games.images',
        ]);

        if ($playlist->name === 'Recently Added') {
            $playlist->setRelation(
                'games',
                $this->getRecentlyAddedGames()
            );
        } elseif ($playlist->name === 'Top Rated') {
            $playlist->setRelation(
                'games',
                $this->getTopRatedGames()
            );
        }

        return new PlaylistResource($playlist);
    }

    /**
     * Return the 10 most recently added released games.
     */
    private function getRecentlyAddedGames()
    {
        return Game::query()
            ->where('status', 'released')
            ->with([
                'ratings',
                'genres',
                'platforms',
                'images',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(self::DYNAMIC_PLAYLIST_LIMIT)
            ->get();
    }

    /**
     * Return the 10 highest-rated games using Google Play ratings.
     *
     * Only ratings with at least 10,000 votes are eligible.
     */
    private function getTopRatedGames()
    {
        $eligibleRatings = function (Builder $query): void {
            $query->where('source', 'Google Play')
                ->where('rating_count', '>=', 10000)
                ->whereNotNull('rating');
        };

        $ratingSubquery = Rating::query()
            ->select('rating')
            ->whereColumn('ratings.game_id', 'games.id')
            ->where('source', 'Google Play')
            ->where('rating_count', '>=', 10000)
            ->whereNotNull('rating')
            ->orderByDesc('rating_count')
            ->limit(1);

        $ratingCountSubquery = Rating::query()
            ->select('rating_count')
            ->whereColumn('ratings.game_id', 'games.id')
            ->where('source', 'Google Play')
            ->where('rating_count', '>=', 10000)
            ->whereNotNull('rating')
            ->orderByDesc('rating_count')
            ->limit(1);

        return Game::query()
            ->whereHas('ratings', $eligibleRatings)
            ->with([
                'ratings',
                'genres',
                'platforms',
                'images',
            ])
            ->orderByDesc($ratingSubquery)
            ->orderByDesc($ratingCountSubquery)
            ->orderBy('id')
            ->limit(self::DYNAMIC_PLAYLIST_LIMIT)
            ->get();
    }
}
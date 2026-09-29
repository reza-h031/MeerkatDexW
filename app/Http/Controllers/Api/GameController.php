<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Http\Resources\SimpleGameResource;
use App\Models\Game;
use App\Models\SimpleGame;
use App\Models\filters\GameFilter;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::with([
            'developer',
            'publisher',
            'platforms',
            'genres',
            'images',
            'ratings',
        ])->get();

        return GameResource::collection($games);
    }


    public function show(Game $game)
    {
        $game->load([
            'developer',
            'publisher',
            'platforms',
            'genres',
            'images',
            'ratings',
        ]);

        return new GameResource($game);
    }


    public function filter(Request $request)
    {
        $filter = GameFilter::fromArray($request);

        $games = Game::with([
            'platforms',
            'genres',
            'images',
            'ratings',
        ])
        ->when(
            $filter->getName() !== '',
            function ($query) use ($filter) {
                $query->where(
                    'title',
                    'like',
                    '%' . $filter->getName() . '%'
                );
            }
        )
        ->get();


		$simpleGames = $games->map(function ($game) {

			$cover = $game->images
				->firstWhere('type', 'cover');

			$icon = $game->images
				->firstWhere('type', 'icon');


            $ratings = $game->ratings->map(function ($rating) {
                return [
                    'id' => $rating->id,
                    'source' => $rating->source,
                    'logoSource' => $rating->logo_source,
                    'rating' => (string) $rating->rating,
                    'ratingCount' => (string) $rating->rating_count,
                ];
            });


            $genres = $game->genres->map(function ($genre) {
                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                ];
            });


            $platforms = $game->platforms->map(function ($platform) {
                return [
                    'id' => $platform->id,
                    'name' => $platform->name,
                    'logo' => $platform->logo,
                ];
            });


            return new SimpleGame(
                $game->id,
                $game->title,
                $cover?->path,
                $icon?->path,
                $ratings,
                $genres,
                $platforms
            );
        });


        return SimpleGameResource::collection($simpleGames);
    }
}
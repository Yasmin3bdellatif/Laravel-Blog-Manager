<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StatsController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('stats', 60, function () {
            $allUsers = User::count();
            $allPosts = Post::count();
            $usersWithNoPosts = User::doesntHave('posts')->count();

            return [
                'all_users' => $allUsers,
                'all_posts' => $allPosts,
                'users_with_no_posts' => $usersWithNoPosts,
            ];
        });
        return response()->json($stats);

    }
}

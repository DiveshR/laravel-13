<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function index(Request $request): View
    {
        $query = $request->string('q')->toString();
        $users = $this->userService->listUsers($query ?: null);

        if ($request->ajax()) {
            return view('admin.users.partials.rows', compact('users'));
        }

        return view('admin.users.index', compact('users', 'query'));
    }
}

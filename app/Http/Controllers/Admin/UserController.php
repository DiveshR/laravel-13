<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminListingService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly AdminListingService $adminListingService,
    )
    {
    }

    public function index(Request $request): View|Response
    {
        $query = $request->string('q')->toString();

        if ($request->ajax()) {
            $page = max(1, (int) $request->integer('page', 1));
            $perPage = 20;
            $useCache = ! $request->boolean('bypass_cache');

            $html = $this->adminListingService->usersRowsHtml($query, $page, $perPage, $useCache);

            return response($html);
        }

        $users = $this->userService->listUsers($query ?: null);
        return view('admin.users.index', compact('users', 'query'));
    }
}

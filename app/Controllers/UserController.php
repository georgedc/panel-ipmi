<?php

namespace App\Controllers;

use App\Http\Request;
use App\Services\AuthService;
use App\Services\UserManager;
use Exception;

class UserController extends Controller
{
    public function __construct(private ?UserManager $users = null, private ?AuthService $authService = null)
    {
        $this->users ??= new UserManager();
        $this->authService ??= new AuthService();
    }

    public function index(Request $request)
    {
        return $this->view('users/index', [
            'title' => 'Users',
            'users' => $this->users->listUsers(),
            'flash_success' => $this->pullFlash('mvc_users_success'),
            'flash_error' => $this->pullFlash('mvc_users_error'),
        ]);
    }

    public function edit(Request $request)
    {
        $user = $this->users->find((int) $request->query('id', 0));
        if (!$user) {
            return $this->redirect(routeUrl('/users'));
        }

        return $this->view('users/edit', [
            'title' => 'Edit User',
            'user' => $user,
            'flash_success' => $this->pullFlash('mvc_users_success'),
            'flash_error' => $this->pullFlash('mvc_users_error'),
        ]);
    }

    public function assignments(Request $request)
    {
        $user = $this->users->find((int) $request->query('id', 0));
        if (!$user) {
            return $this->redirect(routeUrl('/users'));
        }

        return $this->view('users/assignments', [
            'title' => 'Assign Servers',
            'user' => $user,
            'servers' => $this->users->listServers(),
            'assigned' => $this->users->assignedServersMap((int) $user['id']),
            'flash_success' => $this->pullFlash('mvc_users_success'),
            'flash_error' => $this->pullFlash('mvc_users_error'),
        ]);
    }

    public function create(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        try {
            $this->users->create($request->all());
            $this->flash('mvc_users_success', __('users.created'));
            $this->clearFlash('mvc_users_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_users_success');
            $this->flash('mvc_users_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/users'));
    }

    public function update(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        try {
            $this->users->update($request->all());
            $this->flash('mvc_users_success', __('users.updated'));
            $this->clearFlash('mvc_users_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_users_success');
            $this->flash('mvc_users_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/users/edit', ['id' => (int) $request->input('user_id', 0)]));
    }

    public function saveAssignments(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        try {
            $this->users->assignServers((int) $request->input('user_id', 0), $request->all()['servers'] ?? []);
            $this->flash('mvc_users_success', __('users.assigned'));
            $this->clearFlash('mvc_users_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_users_success');
            $this->flash('mvc_users_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/users/assignments', ['id' => (int) $request->input('user_id', 0)]));
    }

    public function delete(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        try {
            $this->users->delete((int) $request->input('user_id', 0));
            $this->flash('mvc_users_success', __('users.deleted'));
            $this->clearFlash('mvc_users_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_users_success');
            $this->flash('mvc_users_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/users'));
    }
}

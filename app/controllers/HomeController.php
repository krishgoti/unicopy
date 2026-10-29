<?php
/**
 * UniHelp - Home Controller
 * Handles landing page and public routes
 */

class HomeController extends Controller {

    public function index(): void {
        // Redirect logged-in users to their dashboard
        if (Auth::check()) {
            $role = Auth::role();
            $this->redirect(BASE_URL . '/dashboard/' . $role);
            return;
        }
        $this->view('home/index');
    }
}

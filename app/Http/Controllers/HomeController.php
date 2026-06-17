<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'seo' => [
                'title' => 'DrivingFaith - Run your whole church from one place',
                'description' => 'DrivingFaith is the back-office platform built for churches. Manage people, communication, events, governance, and outreach in one calm, integrated system.',
                'keywords' => 'church management, ministry software, church events, member directory, church waitlist',
                'image' => asset('images/drivingfaith-icon-square.png'),
                'canonical' => $request->url(),
            ],
        ]);
    }
}

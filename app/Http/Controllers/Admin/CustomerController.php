<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class CustomerController extends Controller
{
    public function index()
    {
        // Get paginated customers (20 per page)
        $customers = User::role('customer')
            ->latest()
            ->paginate(20);

        // Transform each user into the desired array format
        $customers->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
            ];
        });

        // Pass the paginator directly – it already contains data, links, and meta
        return inertia('admin/Customers', [
            'customers' => $customers
        ]);
    }
}
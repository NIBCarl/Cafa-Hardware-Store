<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Public channels for staff dashboard
Broadcast::channel('dashboard', function ($user) {
    // All authenticated staff can listen to dashboard events
    return $user instanceof \App\Models\User;
});

Broadcast::channel('inventory', function ($user) {
    // All authenticated staff can listen to inventory events
    return $user instanceof \App\Models\User;
});

Broadcast::channel('orders', function ($user) {
    // All authenticated staff can listen to order events
    return $user instanceof \App\Models\User;
});

Broadcast::channel('alerts', function ($user) {
    // All authenticated staff can listen to alert events
    return $user instanceof \App\Models\User;
});

// Private channel for customers
Broadcast::channel('customer.{customerId}', function ($customer, $customerId) {
    // Customer can only listen to their own channel
    return $customer instanceof \App\Models\Customer && (int) $customer->id === (int) $customerId;
});

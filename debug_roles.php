<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== User and Role Debug ===\n";

// Get first user
$user = User::first();
if (!$user) {
  echo "No users found in database\n";
  exit;
}

echo "First user: {$user->email} (ID: {$user->id})\n";

// Check if user has roles
try {
  if (method_exists($user, 'roles')) {
    $roles = $user->roles;
    echo "User roles: " . $roles->pluck('name')->join(', ') . "\n";
  } else {
    echo "User model doesn't have roles method\n";
  }

  if (method_exists($user, 'hasRole')) {
    $hasAdmin = $user->hasRole('admin');
    echo "Has admin role: " . ($hasAdmin ? 'Yes' : 'No') . "\n";
  } else {
    echo "User model doesn't have hasRole method\n";
  }

  if (method_exists($user, 'assignRole')) {
    echo "Assigning admin role...\n";
    $user->assignRole('admin');
    echo "Admin role assigned successfully!\n";
  } else {
    echo "User model doesn't have assignRole method\n";
  }
} catch (Exception $e) {
  echo "Error: " . $e->getMessage() . "\n";
}

echo "=== Debug Complete ===\n";

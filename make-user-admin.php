<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "Making User an Admin\n";
echo "===================\n\n";

// Get user ID from command line argument or default to 1
$userId = $argv[1] ?? 1;

echo "Looking for user with ID: $userId\n";

$user = User::find($userId);

if (!$user) {
  echo "✗ User with ID $userId not found\n";
  exit(1);
}

echo "✓ Found user: {$user->first_name} {$user->last_name} ({$user->email})\n";
echo "Current admin status: " . ($user->is_admin ? 'Yes' : 'No') . "\n\n";

// Make user admin
$user->is_admin = true;
$user->save();

echo "✓ User is now a website administrator!\n";
echo "User can now access all parts of the website regardless of community permissions.\n\n";

// Verify the change
$user->refresh();
echo "Verification:\n";
echo "- is_admin: " . ($user->is_admin ? 'true' : 'false') . "\n";
echo "- isWebsiteAdmin(): " . ($user->isWebsiteAdmin() ? 'true' : 'false') . "\n";

echo "\nAdmin user setup complete!\n";

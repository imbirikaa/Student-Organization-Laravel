<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Community;
use App\Models\Event;
use App\Models\CommunityMembership;

echo "=== Testing Admin Stats Queries ===\n";

try {
  $totalUsers = User::count();
  echo "Total users: {$totalUsers}\n";

  $totalCommunities = Community::count();
  echo "Total communities: {$totalCommunities}\n";

  $totalEvents = Event::count();
  echo "Total events: {$totalEvents}\n";

  $activeEvents = Event::where('start_datetime', '>=', now())->count();
  echo "Active events (future): {$activeEvents}\n";

  $pendingApplications = CommunityMembership::where('status', 'pending')->count();
  echo "Pending applications: {$pendingApplications}\n";

  echo "\n=== Test Successful! ===\n";
} catch (Exception $e) {
  echo "Error: " . $e->getMessage() . "\n";
  echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

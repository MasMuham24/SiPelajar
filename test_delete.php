<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = App\Models\User::where('username', 'testuser')->first();
    $teacher = App\Models\Teacher::create([
        'user_id' => $user->id, 'nip' => '123456789', 'gender' => 'L', 'phone' => '0812', 'address' => 'Test'
    ]);
    
    $teacher->user()->delete();
    $teacher->delete();
    
    echo "Deleted successfully.\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

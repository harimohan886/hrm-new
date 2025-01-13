<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config
set('repository', 'git@gitlab.junglesafariindia.in:abhishek.sinha/hrm-system.git');
set('keep_releases', 3); // Keep only 3 releases

// Define shared directories and files
add('shared_dirs', ['storage']);  // Ensure that storage is shared across releases
add('shared_files', []);

// Host configuration
host('production')
    ->set('hostname', '13.232.130.57')
    ->set('remote_user', 'ubuntu')
    ->set('deploy_path', '/var/www/html/hrm-system4')
    ->set('identity_file', '/home/ubuntu/.ssh/id_ed25519');  // Ensure this path is correct

// Tasks
desc('Deploy the application');
task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'deploy:lock',
    'artisan:storage:link',  // Link storage to shared directory
    'artisan:view:cache',
    'artisan:config:cache',
    'deploy:unlock',
    'deploy:symlink',
    'deploy:cleanup',
    'deploy:optimize_clear'
]);

desc('Clear and optimize Laravel cache');
task('deploy:optimize_clear', function () {
    run('cd {{release_path}} && php artisan optimize:clear');
});

// Custom lock tasks
desc('Create deploy lock');
task('deploy:lock', function () {
    run('echo "ci" > {{deploy_path}}/.dep/deploy.lock');
});

desc('Remove deploy lock');
task('deploy:unlock', function () {
    run('rm -f {{deploy_path}}/.dep/deploy.lock');
});

desc('Create symbolic link to the current release');
task('deploy:symlink', function () {
    run('cd {{deploy_path}} && ln -sfn {{release_path}} current');
});

// Handle failed deployments
after('deploy:failed', 'deploy:unlock');
